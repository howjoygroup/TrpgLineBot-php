<?php

function KeyWordReply($inputStr) { 
	$inputStr = strtolower($inputStr);
	
	//讀入manual.json
	$handle = fopen("./ReplyJson/manual.json","r");	
	$content = "";
	while (!feof($handle)) {
		$content .= fread($handle, 10000);
		}
	fclose($handle);	
	$manual = json_decode($content, true);

	//一般功能說明
	if(stristr($inputStr, '說明') != false) {
		return buildTextMessage($manual[0]['說明']);
	}
	
	//更新日誌與公告，使用外聯檔案
	//可以是為一個使用外聯檔案的範例
	if(stristr($inputStr, '更新與公告') != false) {
		
		$file = fopen("https://www.dropbox.com/s/h9m9lfhj8pvlu8k/updated.txt?dl=1", "r");
		$reply = '';

		//輸出文本中所有的行，直到文件結束為止。
		while(! feof($file))
		{
			$reply =  $reply.fgets($file);
		}
		//當讀出文件一行後，就在後面加上 <br> 讓html知道要換行
		fclose($file);
		
		return buildTextMessage($reply);
	}
	
			
	foreach($manual as $systems){
		foreach($systems['系統縮寫'] as $chack){
	
			if(stristr($inputStr, $chack) != false){
			return buildTextMessage($systems['說明']);
			break;
			}
		}
	}	
          
    //鴨霸獸幫我選～～
	if(stristr($inputStr, '選') != false||
		stristr($inputStr, '決定') != false||
		stristr($inputStr, '挑') != false) {
		
		$rplyArr = explode(' ',$inputStr);
    
		if (count($rplyArr) == 1) {return buildTextMessage('選擇的格式不對啦！');}
    
		$Answer = $rplyArr[Dice(count($rplyArr))-1];
		
		if(stristr($Answer, '選') != false||
		stristr($Answer, '決定') != false||
		stristr($Answer, '挑') != false||
		stristr($Answer, '秘書') != false) {
			$rplyArr = Array(
                 '人生是掌握在自己手裡的',
                 '每個都很好哦',
                 '不要把這麼重要的事情交給我決定比較好吧');
		$Answer = $rplyArr[Dice(count($rplyArr))-1];
		}
    return buildTextMessage('我想想喔……我覺得'.$Answer.'。');
	}
	else    
	//以下是運勢功能
	if(stristr($inputStr, '運勢') != false){
		$rplyArr=Array('超大吉','大吉','大吉','中吉','中吉','中吉','小吉','小吉','小吉','小吉','凶','凶','凶','大凶','大凶','你還是，不要知道比較好','這應該不關我的事');
		return buildTextMessage('運勢喔…我覺得，'.$rplyArr[Dice(count($rplyArr))-1].'吧。');
	} 
	
    //以下是關鍵字回覆功能，檔案在 /ReplyJson/textReply.json
	//你也可以直接把json檔案在自己的dropboox之類的地方，用外聯的方式來鏈接
	
	//讀入json
	$handle = fopen("./ReplyJson/textReply.json","r");	
	$content = "";
	while (!feof($handle)) {
		$content .= fread($handle, 10000);
	}
	fclose($handle);	
	$content = json_decode($content, true);
		
	foreach($content as $txtChack){
		foreach($txtChack['chack'] as $chack){
	
			if(stristr($inputStr, $chack) != false){
			return buildTextMessage($txtChack['text'][Dice(count($txtChack['text'])-1)]);
			break;
			}
		}
	}
	
  //沒有觸發關鍵字則是這個
	
	$rplyArr = $content[0]['text'];
	return null; /**buildTextMessage($rplyArr[Dice(count($rplyArr))-1])**/
	
}

//圖片關鍵字功能
function SendImg($inputStr) {
	
	//以下是關鍵字回覆功能，檔案在 /ReplyJson/imgReply.json
	//讀入json
	$handle = fopen("./ReplyJson/imgReply.json","r");	
	$content = "";
	while (!feof($handle)) {
		$content .= fread($handle, 10000);
		}
	fclose($handle);	
	$content = json_decode($content, true);	
	
	foreach($content as $ImgChack){
		foreach($ImgChack['chack'] as $chack){
			
			if(stristr($inputStr, $chack) != false){
			$arrNum = Dice(count($ImgChack['img']))-1;
			error_log("回復陣列第".$arrNum);
			return buildImgMessage($ImgChack['img'][$arrNum]);
			break;
			}
		}
	}
	
	return null;
}

//麻將玩家查詢系統
function mahjong($inputStr) {
      $textall="查詢失敗";
            $json = file_get_contents('https://spreadsheets.google.com/feeds/list/1XCyhDXNJNP-tT3PG8VCe82y79fNoVigo-w8QF_SiyTU/1/public/values?alt=json');
            $data = json_decode($json, true);
            foreach ($data['feed']['entry'] as $item) {
                $keywords = explode(',', $item['gsx$遊戲id']['$t']);
     		 foreach ($keywords as $keyword) {
                 	if ($inputStr== $keyword) {  
                       		$textall = "遊戲ID：".$item['gsx$遊戲id']['$t'].
                      		"\n本名：".$item['gsx$本名']['$t'].
                      		"\n歐付寶ID：".$item['gsx$歐付寶id']['$t'].
                       		"\n代理：".$item['gsx$代理']['$t'];
                     	}
                }
            }
            return buildTextMessage($textall);
}

function mahjong2($inputStr) {
      $textall2="查無此人";
            $json2 = file_get_contents('https://spreadsheets.google.com/feeds/list/1Z5YggH8y_f0_T46_yxLs9dc1cDgSaxBcANjA4UKFnfI/1/public/values?alt=json');
            $data2 = json_decode($json, true);
            foreach ($data2['feed']['entry'] as $item2) {
                $keywords2 = explode(',', $item2['gsx$遊戲id']['$t']);
     		 foreach ($keywords2 as $keyword2) {
			$keyword2 = "m"+$keyword2; 
                 	if ($inputStr == $keyword2) {  
                       		$textall2 = "遊戲ID：".$item2['gsx$遊戲id']['$t'].
                      		"\n本名：".$item2['gsx$本名']['$t'].
                      		"\n歐付寶ID：".$item2['gsx$歐付寶id']['$t'].
                       		"\n代理：".$item2['gsx$代理']['$t'];
                     	}
                }
            }
            return buildTextMessage($keyword2);
}

//手機才看得到的訊息。
function mobile($inputStr) { 
		error_log("手機版專用訊息 ");
		if(stristr($inputStr, '系統說明mobile') != false){
			
			$message ='
			{
  "type": "template",
  "altText": "系統說明",
  "template": {
      "type": "carousel",
      "columns": [
          {
            "title": "《CoC7th 克蘇魯的呼喚》",
            "text": "本系統相關指令，關鍵字為 CC",
            "actions": [
                {
                    "type": "message",
                    "label": "系統指令說明",
                    "text": "秘書CC"
                },
                {
                    "type": "message",
                    "label": "獎懲骰範例",
                    "text": "CC(2)<=50 獎勵骰示範"
                },
                {
                    "type": "message",
                    "label": "技能成長範例",
                    "text": "CC>20 技能成長示範"
                }
            ]
          },
          {
			"title": "《PBTA系統》",
			"text": "本系統相關指令，關鍵字為 pb",
			"actions": [
				{
					"type": "message",
					"label": "系統指令說明",
					"text": "秘書pb"
				},
				{
					"type": "message",
					"label": "一般擲骰範例",
					"text": "pb 示範"
				},
				{
					"type": "message",
					"label": "調整值範例",
					"text": "pb+1 調整值示範"
				}
						
			]
		},
		{
			"title": "《附加功能》",
			"text": "附加功能相關指令，關鍵字為「秘書」以及 .jpg 和 (ry",
			"actions": [
				{
					"type": "message",
					"label": "附加功能指令說明",
					"text": "秘書其他"
				},
				{
					"type": "message",
					"label": "隨機選擇範例",
					"text": "秘書，請幫我選宵夜要吃 鹽酥雞 滷味 滷肉飯"
				},
				{
					"type": "message",
					"label": "圖片回應範例",
					"text": "我覺得不行"
				}
						
			]
		}
      ]
  }
}';
			$message = json_decode($message , true);
			$send = new MutiMessage();
			$replyArr = Array($message);
			
			return $send->send($replyArr );
		}
}
