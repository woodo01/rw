<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) { die(); }

use Bitrix\Main\Localization\Loc;
use Yandex\Market;

/** @var string $boxInputName */
/** @var int $boxNumber */
/** @var array $box */

$disabledProperties = $arResult['SHIPMENT_EDIT'] ? [ 'WEIGHT' => true, 'SIZE' => true ] : [];

?>
<table
	class="yamarket-box-table adm-s-order-table-ddi-table adm-s-bus-ordertable-option js-yamarket-box <?= isset($box['PLACEHOLDER']) ? 'is--hidden' : ''; ?>"
	data-plugin="OrderView.Box"
	data-name="BOX"
>
	<thead>
		<tr>
			<td class="yamarket-box-header">
				<h3 class="yamarket-box-header__title js-yamarket-box__title">
					<?= Loc::getMessage('YANDEX_MARKET_T_TRADING_ORDER_VIEW_BOX'); ?>
					<span class="js-yamarket-box__number">&numero;<?= $box['NUMBER']; ?></span>
				</h3>
				<div class="yamarket-box-header__properties">
					<?php
					foreach ($arResult['BOX_PROPERTIES'] as $boxPropertyName => $boxProperty)
					{
						if (isset($disabledProperties[$boxPropertyName])) { continue; }

						$boxPropertyValue = isset($box['PROPERTIES'][$boxPropertyName]) ? $box['PROPERTIES'][$boxPropertyName] : null;
						$isBoxPropertyEmpty = ((string)$boxPropertyValue === '');

						?>
						<div
							class="yamarket-box-property <?= $isBoxPropertyEmpty ? 'is--hidden' : ''; ?> js-yamarket-box__property"
							data-name="<?= $boxPropertyName; ?>"
						>
							<?= $boxProperty['NAME'] . ': '; ?>
							<span class="js-yamarket-box__property-value"><?= $boxPropertyValue; ?></span>
							<?= isset($boxProperty['UNIT_FORMATTED']) ? $boxProperty['UNIT_FORMATTED'] : ''; ?>
						</div>
						<?php
					}
					?>
				</div>
				<?php
				if ($arResult['SHIPMENT_EDIT'])
				{
					foreach (['FULFILMENT_ID'] as $fieldName)
					{
						?>
						<input
							class="is--persistent js-yamarket-box__input"
							type="hidden"
							<?php
							if (!isset($box['PLACEHOLDER']))
							{
								?>
								name="<?= $boxInputName . '[' . $fieldName .']'; ?>"
								value="<?= $box[$fieldName]; ?>"
								<?
							}
							?>
							data-name="<?= $fieldName; ?>"
						/>
						<?php
					}
					?>
					<div class="yamarket-box-header__actions">
						<input
							class="yamarket-box-action adm-btn adm-btn-delete js-yamarket-box__delete"
							type="button"
							value="<?= Loc::getMessage('YANDEX_MARKET_T_TRADING_ORDER_VIEW_BOX_DELETE'); ?>"
						/>
					</div>
					<?php
				}
				?>
			</td>
		</tr>
	</thead>
	<?php
	if ($arResult['SHIPMENT_EDIT'])
	{
		?>
		<tbody>
			<tr></tr><?php // hack for bitrix css ?>
			<tr class="bdb-line js-yamarket-box__child" data-plugin="OrderView.BoxSize" data-name="DIMENSIONS">
				<td class="tal">
					<div class="yamarket-box-sizes">
						<?php
						foreach ($arResult['BOX_DIMENSIONS'] as $dimensionName => $dimensionDescription)
						{
							$dimensionValue = isset($box['DIMENSIONS'][$dimensionName])
								? $box['DIMENSIONS'][$dimensionName]['VALUE']
								: null;

							?>
							<div class="yamarket-box-size">
								<label class="yamarket-box-size__label"><?= $dimensionDescription['NAME'] . (isset($dimensionDescription['UNIT_FORMATTED']) ? ', ' . $dimensionDescription['UNIT_FORMATTED'] : ''); ?></label>
								<input
									class="yamarket-box-size__input js-yamarket-box-size__input"
									type="text"
									<?php
									if (!isset($box['PLACEHOLDER']))
									{
										?>
										name="<?= $boxInputName . '[DIMENSIONS][' . $dimensionName . ']'; ?>"
										value="<?= htmlspecialcharsbx($dimensionValue); ?>"
										<?php
									}
									?>
									size="6"
									data-name="<?= $dimensionName; ?>"
								/>
							</div>
							<?php
						}
						?>
					</div>
				</td>
			</tr>
		</tbody>
		<?php
	}
	?>
</table>
