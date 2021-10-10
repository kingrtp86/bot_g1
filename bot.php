<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require 'db_out.php';

$db = new db_out("localhost", "kingrtp86", "1qaz@WSX", "g1");

   $accessToken = "e8J+sasQtTGyNRr1tu/qAqjad2cy3AVB+MR9+P87v+P4TPqro+K+7UROcvDlSxigBDOV5+CPDuW79B7Ni24lK/hLAdfSi0YBTMPRr/IXAD8W8219VrIWn4LuBq+ZnxFFyyBJAcM3CiBY1QCS26jecwdB04t89/1O/w1cDnyilFU=";//copy ข้อความ Channel access token ตอนที่ตั้งค่า
   $content = file_get_contents('php://input');
   $arrayJson = json_decode($content, true);
   $arrayHeader = array();
   $arrayHeader[] = "Content-Type: application/json";
   $arrayHeader[] = "Authorization: Bearer {$accessToken}";
   //รับข้อความจากผู้ใช้
   $message = $arrayJson['events'][0]['message']['text'];
   //รับ id ว่ามาจากไหน
   if(isset($arrayJson['events'][0]['source']['room'])){
      $id = $arrayJson['events'][0]['source']['room'];
      $d = 'room';
   }
   else if(isset($arrayJson['events'][0]['source']['groupId'])){
      $id = $arrayJson['events'][0]['source']['groupId'];
      $d = 'group';
   }
   else if(isset($arrayJson['events'][0]['source']['userId'])){
      $id = $arrayJson['events'][0]['source']['userId'];
      $d = 'userId';
   }
   #ตัวอย่าง Message Type "Text + Sticker"
   if($message == "::uid"){
      $sid = $arrayJson['events'][0]['replyToken'];
      $arrayPostData['replyToken'] = $sid;
      $arrayPostData['messages'][0]['type'] = "text";
      $arrayPostData['messages'][0]['text'] = "ทดสอบ";
      // $arrayPostData['messages'][1]['type'] = "sticker";
      // $arrayPostData['messages'][1]['packageId'] = "2";
      // $arrayPostData['messages'][1]['stickerId'] = "34";
      replyMsg($arrayHeader,$arrayPostData);
   }

   if($message == "ปิด"){
      $sid = $arrayJson['events'][0]['replyToken'];
      $arrayPostData['replyToken'] = $sid;


      $sid = $arrayJson['events'][0]['source']['userId'];
      $gid =  $arrayJson['events'][0]['source']['groupId'];
      $c = $db->query("SELECT * FROM `admin` WHERE `uid` = '$sid'")->find();

      if (empty($c)) {
         $arrayPostData['messages'][0]['type'] = "text";
         $arrayPostData['messages'][0]['text'] = "คุณไม่ใช่แอดมิน";
      } else {
         $db->query("UPDATE `temping` SET `temp_text` = '' WHERE `gid` = '$gid';");
         $arrayPostData['messages'][0]['type'] = "text";
         $arrayPostData['messages'][0]['text'] = "ปิดการทำงานแล้ว";
      }

      replyMsg($arrayHeader,$arrayPostData);
   }

   if(preg_match("/^(เช็ค)/",$message)){
      $e = explode('#', $message);

      $sidd = $arrayJson['events'][0]['replyToken'];
      $arrayPostData['replyToken'] = $sidd;

      $sid = $arrayJson['events'][0]['source']['userId'];
      // $arrayPostData['messages'][0]['type'] = "text";
      // $arrayPostData['messages'][0]['text'] = $e[1];

      $find = 'action=admin&name='.$e[1];
      $pf = getProfile($arrayHeader, $arrayJson['events'][0]['source']['userId']);
      $pfname = json_decode($pf);

      $ff = adminfinditem($find);

      // $uid = $arrayJson['events'][0]['source']['userId']);

      $c = $db->query("SELECT * FROM `admin` WHERE `uid` = '$sid'")->find();

      if (empty($c)) {
         $arrayPostData['messages'][0]['type'] = "text";
         $arrayPostData['messages'][0]['text'] = "คุณไม่ใช่แอดมิน";
      } else {
         if ($ff == 404) {
            $h = 'รายการวัสดุไม่ถูกต้อง';
            $b1 = "ไม่พบรายการ \n\n - " . $e[1];
            $b2 = 'กรุณาตรวจสอบข้อมูล';
            $b3 = ' ';
            $color = '#ff751a';
         } else {
            $h = 'เริ่มตรวจสอบรายการ';
            $b1 = 'รายการวัสดุ : ' . $e[1];
            $b2 = 'กรุณาแจ้งขนาดสินค้า และ จำนวนคงเหลือ';
            $b3 = ' ';
            $color = '#12B4D1';
            $gid =  $arrayJson['events'][0]['source']['groupId'];
            
   
            $q = $db->query("SELECT * FROM temping WHERE gid = '$gid'")->find();
            if (empty($q)) {
               $db->query("INSERT INTO `temping` (`id`, `gid`, `uid`, `temp_text`) VALUES (NULL, '$gid', '$sid', '$e[1]')");
            } else {
               $db->query("UPDATE `temping` SET `temp_text` = '$e[1]' WHERE `gid` = '$gid';");
            }
         }
   
         $jayParsedAry = [
            "type" => "flex", 
            "altText" => "Flex Message", 
            "contents" => [
                  "type" => "bubble", 
                  "header" => [
                     "type" => "box", 
                     "layout" => "vertical", 
                     "contents" => [
                        [
                           "type" => "text", 
                           "text" => $h, 
                           "size" => "xl", 
                           "weight" => "bold", 
                           "align" => "center", 
                           "color" => "#FFFFFF" 
                        ] 
                     ] 
                  ], 
                  "body" => [
                              "type" => "box", 
                              "layout" => "vertical", 
                              "contents" => [
                                 [
                                    "type" => "text", 
                                    "text" => $b1,
                                    "size" => "lg", 
                                    "align" => "start", 
                                    "margin" => "lg",
                                    "wrap" => true
                                 ], 
                                 [
                                       "type" => "text", 
                                       "text" => $b2, 
                                       "size" => "lg", 
                                       "align" => "start", 
                                       "margin" => "lg",
                                       "wrap" => true
                                    ], 
                                 [
                                          "type" => "text", 
                                          "text" => $b3, 
                                          "size" => "lg", 
                                          "align" => "start", 
                                          "margin" => "lg" 
                                 ],
                                 [
                                    "type" => "text", 
                                    "text" => "แอดมิน : ".$pfname->{'displayName'}, 
                                    "size" => "lg", 
                                    "align" => "start", 
                                    "margin" => "lg",
                                    "color" => "#A10E22",
                                    "weight" => "bold"
                                 ]
                                 
                              ] 
                           ], 
                  "styles" => [
                                             "header" => [
                                                "backgroundColor" => $color 
                                             ] 
                                          ] 
               ] 
         ]; 
   
         $arrayPostData['messages'][0] = $jayParsedAry;
         // $arrayPostData['messages'][1]['type'] = "text";
         // $arrayPostData['messages'][1]['text'] =  $arrayJson['events'][0]['source']['groupId'];
      }

      
      replyMsg($arrayHeader,$arrayPostData);
   }

   if (preg_match("/^#/",$message)) {
      $e = explode('#', $message);

      $pf = getProfile($arrayHeader, $arrayJson['events'][0]['source']['userId']);
      $pfname = json_decode($pf);

      $sidd = $arrayJson['events'][0]['replyToken'];
      $arrayPostData['replyToken'] = $sidd;
      // $arrayPostData['messages'][0]['type'] = "text";
      // $arrayPostData['messages'][0]['text'] = "รับข้อมูล : ".$e[1].'ขนาด : '.$e[2].' จำนวน : '.$e[3] . $pfname->{'displayName'};


      // $arrayPostData['messages'][1]['type'] = "sticker";
      // $arrayPostData['messages'][1]['packageId'] = "2";
      // $arrayPostData['messages'][1]['stickerId'] = "34";

      // $find['item']['name'] = $e[1];
      // $find['item']['type'] = $e[2];
      // $find['item']['amount'] = $e[3];

      $gid =  $arrayJson['events'][0]['source']['groupId'];
      $temping = $db->query("SELECT * FROM `temping` WHERE `gid` = '$gid'")->find();

      if ($temping->temp_text != '') {
         $find = 'action=find&name='.urlencode($temping->temp_text).'&type='.urlencode($e[1]).'&amount='.$e[2].'&uname='.urlencode($pfname->{'displayName'});

         $ff = finditem($find);
   
         if ($ff == 4041) {
            $h = 'รายการวัสดุไม่ถูกต้อง';
            $b1 = "ไม่พบรายการ \n\n - " . $temping->temp_text;
            $b2 = 'กรุณาตรวจสอบข้อมูล';
            $b3 = ' ';
            $color = '#ff751a';
         } else if ($ff == 4042) {
            $h = 'ขนาดวัสดุไม่ถูกต้อง';
            $b1 = "ไม่พบขนาดวัสดุ\n - " . $temping->temp_text . "\n - ขนาด " . $e[1];
            $b2 = 'กรุณาตรวจสอบข้อมูล';
            $b3 = ' ';
            $color = '#ff751a';
         } else {
            $h = 'บันทึกข้อมูลสำเร็จ';
            $b1 = 'รายการวัสดุ : ' . $temping->temp_text;
            $b2 = 'ขนาด : ' . $e[1];
            $b3 = 'จำนวน : ' . $e[2];
            $color = '#12B4D1';
         }
   
         $jayParsedAry = [
            "type" => "flex", 
            "altText" => "Flex Message", 
            "contents" => [
                  "type" => "bubble", 
                  "header" => [
                     "type" => "box", 
                     "layout" => "vertical", 
                     "contents" => [
                        [
                           "type" => "text", 
                           "text" => $h, 
                           "size" => "xl", 
                           "weight" => "bold", 
                           "align" => "center", 
                           "color" => "#FFFFFF" 
                        ] 
                     ] 
                  ], 
                  "body" => [
                              "type" => "box", 
                              "layout" => "vertical", 
                              "contents" => [
                                 [
                                    "type" => "text", 
                                    "text" => $b1,
                                    "size" => "lg", 
                                    "align" => "start", 
                                    "margin" => "lg",
                                    "wrap" => true
                                 ], 
                                 [
                                       "type" => "text", 
                                       "text" => $b2, 
                                       "size" => "lg", 
                                       "align" => "start", 
                                       "margin" => "lg" 
                                    ], 
                                 [
                                          "type" => "text", 
                                          "text" => $b3, 
                                          "size" => "lg", 
                                          "align" => "start", 
                                          "margin" => "lg" 
                                 ],
                                 [
                                    "type" => "text", 
                                    "text" => "@".$pfname->{'displayName'}, 
                                    "size" => "lg", 
                                    "align" => "start", 
                                    "margin" => "lg",
                                    "color" => "#A10E22",
                                    "weight" => "bold"
                                 ]
                                 
                              ] 
                           ], 
                  "styles" => [
                                             "header" => [
                                                "backgroundColor" => $color 
                                             ] 
                                          ] 
               ] 
         ]; 
   
         $arrayPostData['messages'][0] = $jayParsedAry;
      } else {
         $arrayPostData['messages'][0]['type'] = "text";
         $arrayPostData['messages'][0]['text'] = "กรุณารอแอดมินเปิดคำสั่งเช็คจำนวนสินค้า";
      }
      
      replyMsg($arrayHeader,$arrayPostData);


   }
   
function pushMsg($arrayHeader,$arrayPostData){
      $strUrl = "https://api.line.me/v2/bot/message/push";
      $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL,$strUrl);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $arrayHeader);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayPostData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            $result = curl_exec($ch);
            curl_close ($ch);
}

function replyMsg($arrayHeader,$arrayPostData){
   $strUrl = "https://api.line.me/v2/bot/message/reply";
   $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL,$strUrl);
         curl_setopt($ch, CURLOPT_HEADER, false);
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $arrayHeader);
         curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($arrayPostData));
         curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         $result = curl_exec($ch);
         curl_close ($ch);
}

function getProfile($arrayHeader,$uid){
   $strUrl = "https://api.line.me/v2/bot/profile/".$uid;
   $ch = curl_init();
         // curl_setopt($ch, CURLOPT_URL,$strUrl);
         // curl_setopt($ch, CURLOPT_HEADER, false);
         // curl_setopt($ch, CURLOPT_GET, true);
         // curl_setopt($ch, CURLOPT_HTTPHEADER, $arrayHeader);
         // curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
         // curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

         curl_setopt($ch, CURLOPT_URL, $strUrl);
         curl_setopt($ch, CURLOPT_HTTPHEADER, $arrayHeader);
         curl_setopt($ch, CURLOPT_HEADER, 0);

         //$body = '{}';
         //curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET"); 
         //curl_setopt($ch, CURLOPT_POSTFIELDS,$body);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

         $result = curl_exec($ch);
         curl_close ($ch);

         return $result;
}


function finditem($arrayPostData){
   $strUrl = "https://hook.integromat.com/6rii8edc94fhgi3uyvoxrwpkdros5kfa";
   $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL,$strUrl);
         // curl_setopt($ch, CURLOPT_HEADER, array('Content-Type: application/json'));
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         // curl_setopt($ch, CURLOPT_HTTPHEADER, $arrayHeader);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayPostData);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         $result = curl_exec($ch);
         curl_close ($ch);

         return $result;
}

function adminfinditem($arrayPostData){
   $strUrl = "https://hook.integromat.com/6rii8edc94fhgi3uyvoxrwpkdros5kfa";
   $ch = curl_init();
         curl_setopt($ch, CURLOPT_URL,$strUrl);
         // curl_setopt($ch, CURLOPT_HEADER, array('Content-Type: application/json'));
         curl_setopt($ch, CURLOPT_POST, true);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
         // curl_setopt($ch, CURLOPT_HTTPHEADER, $arrayHeader);
         curl_setopt($ch, CURLOPT_POSTFIELDS, $arrayPostData);
         curl_setopt($ch, CURLOPT_RETURNTRANSFER,true);
         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
         $result = curl_exec($ch);
         curl_close ($ch);

         return $result;
}


exit;
?>
