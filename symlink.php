<html>
<head><title>�������� ������ �� ����� bitrix � upload</title></head>
<body>
<?
error_reporting(E_ALL & ~E_NOTICE);
@ini_set("display_errors",1);

if ($_POST['path'])
   $path = rtrim($_POST['path'],"/\\");
else
   $path = '../site2/www';

if ($_POST['create'])
{
   if (preg_match("#^/#",$path))
      $full_path = $path;
   else
      $full_path = realpath($_SERVER['DOCUMENT_ROOT'].'/'.$path);

   if (file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix"))
      $strError = "� ������� ����� ��� ���������� ����� bitrix";
   elseif (is_dir($full_path))
   {
      if (is_dir($full_path."/bitrix"))
      {
         if (symlink($path."/bitrix",$_SERVER['DOCUMENT_ROOT']."/bitrix"))
         {
            if(symlink($path."/upload",$_SERVER['DOCUMENT_ROOT']."/upload"))
               echo "<font color=green>������������� ������ ������ �������</font>";
            else
               $strError = '�� ������� ������� ������ �� ����� upload, ���������� � �������������� �������';
         }
         else
            $strError = '�� ������� ������� ������ �� ����� bitrix, ���������� � �������������� �������';
            
      }
      else
         $strError = '��������� ���� �� �������� ����� bitrix';
   }
   else
      $strError = '������� ������ ���� ��� ������ ���� �������';
   
   if ($strError)
      echo '<font color=red>'.$strError.'</font><br>�������� ����: '.$full_path;
}
?>
<form method=post>
���� � �����, ���������� ����� bitrix � upload: <input name=path value="<?=htmlspecialchars($path)?>"><br>
<input type=submit value='�������' name=create>
</form>