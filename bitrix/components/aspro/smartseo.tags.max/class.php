<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Aspro\Max\Smartseo,
    Bitrix\Main\Localization\Loc as Loc;

Loc::loadMessages(__FILE__);

class SmartseoTagsComponent extends \CBitrixComponent
{

    const PAGE_DEFAULT = 'template';
    const PAGE_ERRORS = 'error';
    const DEFAULT_CODE_VIEW_CONTENT = 'aspro_smartseo_tags';
    const DEFAULT_VIEW_TYPE = 'normal';
    const DEFAULT_BG_FILLED = 'Y';

    private $sectionId = null;
    private $dataInfo = [];
    private $variableValues = [];

    public function getUnique()
    {
        return md5($this->GetName() . '.' . $this->GetTemplateName());
    }

    /**
     * @inheritdoc
     */
    public function executeComponent()
    {
        global $APPLICATION;

        $this->setSefDefaultParams();

        if (!$this->validate()) {
            $this->arResult = [
                'ERROR_MESSAGE' => $this->getErrors(),
            ];

            $this->includeComponentTemplate(self::PAGE_ERRORS);

            return;
        }

        if ($this->StartResultCache($this->arParams['CACHE_TIME'])) {
            $this->setResult();
            $this->includeComponentTemplate(self::PAGE_DEFAULT);
        }
    }

    public function hasErrors()
    {
        if ($this->_errors) {
            return true;
        }

        return false;
    }

    public function getErrors()
    {
        return $this->_errors;
    }

    public function getSummary()
    {
        return $this->_summary;
    }

    public function getCacheID($additionalCacheID = false)
    {
        $currentData = Smartseo\General\Smartseo::getCurrentData();

        if($currentData) {
           $additionalCacheID = $currentData['URL_ID'];


        } elseif($variableValues = $this->getVariableValues()) {
            foreach($variableValues as $k => $v) {
                if(strncmp("~", $k, 1)) {
                    $additionalCacheID .= ",".$k."=".serialize($v);
                }
            }
        }

        return parent::getCacheID($additionalCacheID);
    }

    protected function validate()
    {
        if (!class_exists('\Aspro\Max\Smartseo\General\Smartseo') || !Smartseo\General\Smartseo::validateModules()) {
            $this->_errors[] = Loc::getMessage('ASPRO_SMARTSEO_CONTENT_ERROR_MODULE_NOT_INSTALLED');

            return false;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function setSefDefaultParams()
    {
        $this->arParams['SEF_MODE'] = $this->arParams['MODE'];
        $this->arParams['SEF_FOLDER'] = $this->arParams['FOLDER'];
        $this->arParams['SEF_URL_TEMPLATES'] = $this->arParams['URL_TEMPLATES'];

        if(!isset($this->arParams['CACHE_TIME'])) {
            $this->arParams['CACHE_TIME'] = 36000000;
        }

        if($this->arParams['LIMIT']) {
            $this->arParams['LIMIT'] = (int)$this->arParams['LIMIT'];
        } else {
            $this->arParams['LIMIT'] = 500;
        }

        if(!$this->arParams['CODE_VIEW_CONTENT']) {
            $this->arParams['CODE_VIEW_CONTENT'] = self::DEFAULT_CODE_VIEW_CONTENT;
        }

        if(!$this->arParams['VIEW_TYPE']) {
            $this->arParams['VIEW_TYPE'] = self::DEFAULT_VIEW_TYPE;
        }

        if(!$this->arParams['BG_FILLED']) {
            $this->arParams['BG_FILLED'] = self::DEFAULT_BG_FILLED;
        }

        if($this->arParams['SHOW_COUNT']) {
            $this->arParams['SHOW_COUNT'] = (int)$this->arParams['SHOW_COUNT'];
        } else {
            $this->arParams['SHOW_COUNT'] = 10;
        }

        if($this->arParams['SHOW_COUNT_MOBILE']) {
            $this->arParams['SHOW_COUNT_MOBILE'] = (int)$this->arParams['SHOW_COUNT_MOBILE'];
        } else {
            $this->arParams['SHOW_COUNT_MOBILE'] = 3;
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function setResult()
    {
        $this->arResult = [
            'UNIQUE' => $this->getUnique(),
            'LIST' => $this->getTagList(),
            'INFO' => $this->dataInfo
        ];
    }

    protected function getTagList()
    {
        $currentData = Smartseo\General\Smartseo::getCurrentData();

        $tags = [];

        if ($currentData) {
            $tags = $this->getUrlsByTags($this->getTagListByParentCondition());
        } else {
            if (!$tags) {
                $tags = $this->getUrlsByTags($this->getTagListBySection(), $isAnotherSection = true);
            }

            if (!$tags) {
                $tags = $this->getUrlsByTags($this->getTagListBySelfSection());
            }
        }

        return array_filter($tags);
    }

    protected function getUrlsByTags(array $tags, $isAnotherSection = false)
    {
        $currentData = Smartseo\General\Smartseo::getCurrentData();
        $currentUrlProperty = $this->getPreparePropertyValues($currentData['URL_PROPERTIES']);

        $filterConditionIds = array_keys(array_column($tags, 'TEMPLATE', 'FILTER_CONDITION_ID'));

        $filter = [];
        $filter['FILTER_CONDITION_ID'] = $filterConditionIds;

        if (!$isAnotherSection) {
            $filter['SECTION_ID'] = $this->getSectionId();
        }

        $filterConditionUrls = Smartseo\Models\SmartseoFilterConditionUrlTable::getList([
              'select' => [
                  'ID',
                  'NEW_URL',
                  'FILTER_CONDITION_ID',
                  'PROPERTIES',
                  'FILTER_CONDITION_NAME' => 'FILTER_CONDITION.NAME',
                  'FILTER_RULE_ID' => 'FILTER_CONDITION.FILTER_RULE.ID',
                  'FILTER_RULE_NAME' => 'FILTER_CONDITION.FILTER_RULE.NAME',
              ],
              'filter' => array_merge([
                'FILTER_CONDITION.FILTER_RULE.ACTIVE' => 'Y',
                'FILTER_CONDITION.ACTIVE' => 'Y',
                'ACTIVE' => 'Y',
                'STATE_DELETED' => 'N',
              ],$filter),
              'limit' => $this->arParams['LIMIT'],
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoFilterConditionUrlTable::getCacheTtl(),
              ]
          ])->fetchAll();

        $result = [];
        foreach ($filterConditionUrls as $url) {
            $element = new Smartseo\Template\Entity\FilterRuleUrl($url['ID']);

            foreach ($tags as $tag) {
                if($tag['FILTER_CONDITION_ID'] != $url['FILTER_CONDITION_ID']) {
                    continue;
                }

                if($tag['TYPE'] == Smartseo\Models\SmartseoFilterTagTable::TYPR_FILTER_CONDITION && $tag['RELATED_PROPERTY']) {
                    $_currentUrlProperty = $this->getFilteredPropertyByRelatedValues($currentUrlProperty, $tag['RELATED_PROPERTY']);

                    $_urlProperties = $this->getPreparePropertyValues($url['PROPERTIES']);
                    $_urlProperties = $this->getFilteredPropertyByRelatedValues($_urlProperties, $tag['RELATED_PROPERTY']);

                    if(!$this->arrayIntersect($_currentUrlProperty, $_urlProperties)) {
                      continue;
                    }
                }

                $result[] = [
                    'URL' => $url['NEW_URL'],
                    'NAME' => \Bitrix\Main\Text\HtmlFilter::encode(
                      \Bitrix\Iblock\Template\Engine::process($element, $tag['TEMPLATE'])
                    ),
                    'SELECTED' => $url['ID'] == $currentData['URL_ID']
                ];

                $this->dataInfo[$tag['ID']] = [
                    'FILTER_RULE_ID' => $url['FILTER_RULE_ID'],
                    'FILTER_RULE_NAME' => $url['FILTER_RULE_NAME'],
                    'TAG_ID' => $tag['ID'],
                ];
            }
        }

        return $result;
    }

    protected function getTagListBySelfSection()
    {
        return $this->getTagTableRows([
            '=TYPE' => Smartseo\Models\SmartseoFilterTagTable::TYLE_SELF_SECTION,
            '=FILTER_CONDITION.FILTER_RULE.IBLOCK_SECTIONS.SECTION_ID' => $this->getSectionId()
        ]);
    }

    protected function getTagListByParentCondition()
    {
        $currentData = Smartseo\General\Smartseo::getCurrentData();

        if (!$currentData || !$currentData['CONDITION_ID']) {
            return [];
        }

        return $this->getTagTableRows([
            '=TYPE' => Smartseo\Models\SmartseoFilterTagTable::TYPR_FILTER_CONDITION,
            '=PARENT_FILTER_CONDITION_ID' => $currentData['CONDITION_ID']
        ]);

        return $result;
    }

    protected function getTagListBySection()
    {
        return $this->getTagTableRows([
            '=TYPE' => Smartseo\Models\SmartseoFilterTagTable::TYPE_SECTION,
            '=SECTION_ID' => $this->getSectionId()
        ]);
    }

    protected function getTagTableRows($filter = [])
    {
        $rows = Smartseo\Models\SmartseoFilterTagTable::getList([
              'select' => [
                  'ID',
                  'TYPE',
                  'FILTER_RULE_ID' => 'FILTER_CONDITION.FILTER_RULE.ID',
                  'FILTER_CONDITION_ID',
                  'TEMPLATE',
                  'RELATED_PROPERTY'
              ],
              'filter' => array_merge([
                  'ACTIVE' => 'Y',
                  'FILTER_CONDITION.ACTIVE' => 'Y',
                  'FILTER_CONDITION.FILTER_RULE.ACTIVE' => 'Y',
                ], $filter),
              'cache' => [
                  'ttl' => Smartseo\Models\SmartseoFilterTagTable::getCacheTtl(),
              ]
          ])->fetchAll();

        $result = [];
        foreach ($rows as $row) {
            $result[] = [
                'ID' => $row['ID'],
                'TYPE' => $row['TYPE'],
                'FILTER_RULE_ID' => $row['FILTER_RULE_ID'],
                'FILTER_CONDITION_ID' => $row['FILTER_CONDITION_ID'],
                'TEMPLATE' => $row['TEMPLATE'],
                'RELATED_PROPERTY' => $row['RELATED_PROPERTY'] ? unserialize($row['RELATED_PROPERTY']) : [],
            ];
        }

        return $result;
    }

    protected function getSectionId()
    {
        if ($this->sectionId) {
            return $this->sectionId;
        }

        if ($this->arParams['SECTION_ID']) {
            return $this->arParams['SECTION_ID'];
        }

        $variableValues = $this->getVariableValues();

        if(!$variableValues['SECTION_ID'] && !$variableValues['SECTION_CODE']) {
            return null;
        }

        if(!$variableValues['SECTION_ID']) {
            $section = \Bitrix\Iblock\SectionTable::getRow([
                'select' => [
                    'ID'
                ],
                'filter' => [
                    'CODE' => $variableValues['SECTION_CODE'],
                ],
            ]);

            $this->sectionId = $section['ID'];

            return $this->sectionId;
        }

        $this->sectionId = $variableValues['SECTION_ID'] ?: null;

        return $this->sectionId;
    }

    protected function getVariableValues()
    {
        if($this->variableValues) {
            return $this->variableValues;
        }

        if ($this->arParams['SEF_MODE'] !== 'Y') {
            return [];
        }

        $engine = new \CComponentEngine($this);

        if (\Bitrix\Main\Loader::includeModule('iblock')) {
            $engine->addGreedyPart('#SECTION_CODE_PATH#');
            $engine->addGreedyPart('#SMART_FILTER_PATH#');
            $engine->setResolveCallback(['CIBlockFindTools', 'resolveComponentEngine']);
        }

        $defaultVariableAliases404 = [
            'section' => '#SECTION_ID#/',
        ];

        $urlTemplates = CComponentEngine::makeComponentUrlTemplates($defaultVariableAliases404, $this->arParams['SEF_URL_TEMPLATES']);
        $variableAliases = CComponentEngine::makeComponentVariableAliases($defaultVariableAliases404, $this->arParams['VARIABLE_ALIASES']);

        $result = [];

        $engine->guessComponentPath(
          $this->arParams['SEF_FOLDER'], $urlTemplates, $result
        );

        $this->variableValues = $result;

        return $result;
    }

    protected function getPreparePropertyValues($propertySerialized = '')
    {
        if (!$propertySerialized) {
            return [];
        }

        $urlProperties = unserialize($propertySerialized);

        $result = [];

        foreach ($urlProperties as $property) {
            $result[] = [
                'PROPERTY_ID' => $property['PROPERTY_ID'],
                'VALUE' => $property['_VALUES'],
            ];
        }

        return array_column($result, 'VALUE', 'PROPERTY_ID');
    }

    protected function getFilteredPropertyByRelatedValues(&$properties, array $relatedPropertyIds)
    {
        return array_filter($properties, function($value, $key) use($relatedPropertyIds) {
            if(in_array($key, $relatedPropertyIds)) {
                return true;
            }

            return false;
        }, ARRAY_FILTER_USE_BOTH);
    }

    protected function arrayIntersect($array1, $array2)
    {
        foreach($array1 as $key => $value){
            if(!isset($array2[$key])) {
                return false;
            }

            if(!array_intersect($value, $array2[$key])) {
                return false;
            }
        }

        return true;
    }

}

?>
