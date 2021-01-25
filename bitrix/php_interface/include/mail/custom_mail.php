<?

// Отправка почты 
  function   custom_mail  ( $to , $subject , $body , $headers )  { 
      
    \Bitrix\Main\Diag\Debug::writeToFile(array('ID' => 'custom_mail', $to , $subject , $body, $headers),"","mail.txt");
    $arHeaders  = email_parse_headers( $headers );
    $fromHeader  =  $arHeaders [ 'sender' ][ 0 ] ??  $arHeaders [ 'from' ][ 0 ];
    $fromAddress  = explode( ',' ,  $fromHeader )[ 0 ];
    $fromAddress  = get_from($headers);
    if  (preg_match( '/<([^>]+)>/' ,  $fromAddress ,  $matches )) {
        $fromAddress  =  $matches [ 1 ];
    }
    if  (preg_match( '/<([^>]+)>/' ,  $to ,  $matches )) {
      $to  =  $matches [ 1 ];
    }
    // unset($arHeaders['from']);
    // unset($arHeaders['to']);
    // unset($arHeaders['reply-to']);
    // $headers = "From: ".$fromAddress."\n";
    // $headers .= "To: ".$to."\n";
    // $headers .= "Reply-to: ".$to."\n";
    // $headers .= "Content-Type: text/html; charset=UTF-8\n";
    // $headers .= "Content-Transfer-Encoding: 8bit\n";
    // foreach($arHeaders as $key => $value) {
    //   $headers .= "$key: ".implode(", ", $value)."\n";
    // }
    \Bitrix\Main\Diag\Debug::writeToFile(array($to),"","mail.txt");
    \Bitrix\Main\Diag\Debug::writeToFile(array($fromAddress),"","mail.txt");
    \Bitrix\Main\Diag\Debug::writeToFile(array($headers),"","mail.txt");
    return  mail( $to , $subject , $body , $headers , "-r $fromAddress" );
    $accounts  = getMsmtpAccounts();
     if  ( $accounts  &&  $param  = array_search( $fromAddress ,  $accounts )) {
         $params  =  '--account=' . $param . ' --from=' . $fromAddress;
    }
    \Bitrix\Main\Diag\Debug::writeToFile(array($accounts),"","mail.txt");
    \Bitrix\Main\Diag\Debug::writeToFile(array($params),"","mail.txt");
     return  mail( $to , $subject , $body , $headers , $params );
}

  // Получение хедеров письма 
  function   email_parse_headers  ( $headersStr )  { 
     $headers  = [];
     $currentHeader  =  null ;
     foreach  (explode( "\n" ,  $headersStr )  as   $line ) {
       $line  .=  "\n" ;
       if  (preg_match( '/^([\w-]+):(.*)$/' ,  $line ,  $matches )) {
           if  ( $currentHeader ) {
               $currentHeader [ 'value' ] = rtrim( $currentHeader [ 'value' ],  "\r\n" );
               $headers [ $currentHeader [ 'name' ]][] =  $currentHeader [ 'value' ];
          }
           $currentHeader  = [ 'name'  => strtolower( $matches [ 1 ]),  'value'  => ltrim( $matches [ 2 ])];
      }  else  {
           $currentHeader [ 'value' ] .=  $line ;
      }
    }
     if  ( $currentHeader ) {
       $currentHeader [ 'value' ] = rtrim( $currentHeader [ 'value' ],  "\r\n" );
       $headers [ $currentHeader [ 'name' ]][] =  $currentHeader [ 'value' ];
    }
     return   $headers ;
}

function get_from ($headers) {
  $fromAddress = '';
  foreach  (explode( "\n" ,  $headers )  as   $line ) {
    $line = explode(":", $line);
    if(strtolower($line[0]) == 'from') {
      if  (preg_match( '/<([^>]+)>/' ,  $line[1] ,  $matches )) {
        $fromAddress  =  $matches [ 1 ];
      }
      else {
        $fromAddress = $line[1];
      }
    }
  }
}

  // Получение аккаунтов msmtp 
function   getMsmtpAccounts  ()  { 
    $msmtpConfig  = file_get_contents( "/home/bitrix/.msmtprc" );
    \Bitrix\Main\Diag\Debug::writeToFile(array($msmtpConfig ),"","mail.txt");
    $accounts  =  null ;
    $accountName  =  "" ;
    foreach  (explode( "\n" ,  $msmtpConfig )  as   $line ) {
        $line  .=  "\n" ;
        if  (preg_match( '/^([\w-]+)\s*\t*(.*)$/' ,  $line ,  $matches )) {
            if  ( $matches [ 1 ] ==  "account" ) {
                if  (trim(explode( ":" , $matches [ 2 ])[ 0 ]) ==  "default" ) {
                    //$defaultAccountName = trim(explode(":",$matches[2])[1], " \t\r\n"); 
                    continue ;
                }
                $accountName  =  $matches [ 2 ];
            }  else   if  ( $matches [ 1 ] ==  "from" ) {
                $accounts [ $accountName ] = rtrim( $matches [ 2 ],  "\r\n" );      
            }
        }
    }
    return   $accounts ;
} 