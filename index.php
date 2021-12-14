<?php
require_once('./lib/simple_html_dom.php');

set_error_handler('errorHandler'); 

function createTable($array){
    if(is_array($array) && count($array)>0){
        $errorContent = "<table border = 1><tr><td>";
        foreach ($array as $key => $val) {
            $errorContent .= $key . "</td><td>";
            if(is_array($val) && count($val)>0){
                $errorContent .= createTable(json_decode(json_encode($val),true)) ;
            }else{
                $errorContent .= print_r($val, true) ;
            }
        }
        $errorContent .= "</td></tr></table>";
        return $errorContent;
    }
    return '';
}
 

function errorHandler($errorNumber, $errorString, $errorFile, $errorLine, $errorContext) {
    $emailAddress = 'timofeimih@gmail.com';
    $emailSubject = '[get_reviews] Error on my Application';
    $emailMessage = '<h2>Error Reporting on :- </h2>[' . date("Y-m-d h:i:s", time()) . ']';
    $emailMessage .= "<h2>Error Number :- </h2>".print_r($errorNumber, true).'';
    $emailMessage .= "<h2>Error String :- </h2>".print_r($errorString, true).'';
    $emailMessage .= "<h2>Error File :- </h2>".print_r($errorFile, true).'';
    $emailMessage .= "<h2>Error Line :- </h2>".print_r($errorLine, true).'';
    $emailMessage .= "<h2>Error Context :- </h2>".createTable($errorContext);
    $headers = "MIME-Version: 1.0" . "rn";
    $headers .= "Content-type:text/html;charset=UTF-8" . "rn";
    mail($emailAddress, $emailSubject, $emailMessage, $headers); // you may use SMTP, default php mail service OR other email sending process
}


function russianDateToTimestamp($date){
	$date = explode(' ', trim($date));
	$date[1] = str_replace('января', 'January', $date[1]);
	$date[1] = str_replace('февраля', 'February', $date[1]);
	$date[1] = str_replace('марта', 'March', $date[1]);
	$date[1] = str_replace('апреля', 'April', $date[1]);
	$date[1] = str_replace('мая', 'May', $date[1]);
	$date[1] = str_replace('июня', 'June', $date[1]);
	$date[1] = str_replace('июля', 'July', $date[1]);
	$date[1] = str_replace('августа', 'August', $date[1]);
	$date[1] = str_replace('сентября', 'September', $date[1]);
	$date[1] = str_replace('октября', 'October', $date[1]);
	$date[1] = str_replace('ноября', 'November', $date[1]);
	$date[1] = str_replace('декабря', 'December', $date[1]);

	return strtotime(implode(' ', $date));
}

function get2Gis($link, $office_name){
	// 2gis
	$html = file_get_html($link);
	$data = [];

	foreach($html->find('._1b96w9b', 0)->children(1)->find('._11gvyqv') as $elem){

		$stars = count($elem->find('._1fkin5c', 0)->find('svg[fill=#ffb81c]'));

		if($stars >= 4){
			$data[] = [
				'service' => '2gis',
				'service_full' => '2ГИС',
				'office_name' => $office_name,
				'link' => $link,
				'name' => trim($elem->find('._16s5yj36', 0)->plaintext),
				'stars' => $stars,
				'review' => $elem->find('._1it5ivp', 0) ? trim($elem->find('._1it5ivp', 0)->plaintext) : trim($elem->find('._ayej9u3', 0)->plaintext),
				'date' => $elem->find('._4mwq3d', 0)->plaintext,
				'timestamp' => russianDateToTimestamp($elem->find('._4mwq3d', 0)->plaintext)
			];
		}
		
	}

	return $data;

}


function getYandex($link, $office_name){
	// yandex
	$html = file_get_html($link);
	$data = [];

	foreach($html->find('.business-reviews-card-view__reviews-container', 0)->find('.business-review-view') as $elem){

		$date = $elem->find('.business-review-view__date', 0)->plaintext;
		if(intval(substr($date, -5)) < 2000){
			$date = $date . ' 2021';
		}

		$stars = 5-count($elem->find('.business-review-view__rating', 0)->find('._empty'));

		if($stars >= 4){
			$data[] = [
				'service' => 'yandex',
				'service_full' => 'Яндекс',
				'office_name' => $office_name,
				'link' => $link,
				'name' => trim($elem->find('.business-review-view__author', 0)->find('span[itemprop=name]', 0)->plaintext),
				'stars' => $stars,
				'review' => trim($elem->find('.business-review-view__body-text', 0)->plaintext),
				'date' => $date,
				'timestamp' => russianDateToTimestamp($date)
			];
		}

		
	}

	return $data;
}

function getYa($link, $office_name){
	// ya.ru
	$search = true;
	$page = 0;
	$data = [];
	while($search){

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, 'https://www.yp.ru/detail/reviews/');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, "page=" . $page . $link);
		curl_setopt($ch, CURLOPT_ENCODING, 'gzip, deflate');

		$headers = array();
		$headers[] = 'Connection: keep-alive';
		$headers[] = 'Sec-Ch-Ua: ^^';
		$headers[] = 'Accept: */*';
		$headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=UTF-8';
		$headers[] = 'X-Requested-With: XMLHttpRequest';
		$headers[] = 'Sec-Ch-Ua-Mobile: ?0';
		$headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/96.0.4664.45 Safari/537.36';
		$headers[] = 'Sec-Ch-Ua-Platform: ^^Windows^^\"\"';
		$headers[] = 'Origin: https://www.yp.ru';
		$headers[] = 'Sec-Fetch-Site: same-origin';
		$headers[] = 'Sec-Fetch-Mode: cors';
		$headers[] = 'Sec-Fetch-Dest: empty';
		$headers[] = 'Referer: https://www.yp.ru/detail/id/biznes_dialog_2911171/';
		$headers[] = 'Accept-Language: en-US,en;q=0.9,ru;q=0.8';
		$headers[] = 'Cookie: PHPSESSID=990f568745f1e84d3bcd92331d993db9; YII_CSRF_TOKEN=QlRWbkx6N0F0bFFlc0pKZXd0RHdfblNNQ35tNFZ0STLWHyqRrSznx1e-Q3Dr1xwx-gYMVlKI0k4jMqcd1HPcLg^%^3D^%^3D; _height=969; _width=1920; _ym_d=1638180444; _ym_uid=1638180444676645566; _ga=GA1.2.1459420556.1638180444; _gid=GA1.2.1280681104.1638180444; _ym_visorc=w; _ym_isad=2; _gat_gtag_UA_27951861_1=1; name_en=spb';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		$result = curl_exec($ch);
		if (curl_errno($ch)) {
		    echo 'Error:' . curl_error($ch);
		}
		curl_close($ch);

		if(strpos($result, 'Нет результатов.')){
			$search = false;
			break;
		}

		$html = str_get_html($result);

		foreach($html->find('.review__item') as $elem){

			$name_date = str_replace('&nbsp;', ' ', $elem->find('.review__author', 0)->plaintext);
			$name_date = explode(' (', $name_date);
			$date = explode(' в ', $name_date[1])[0];

			$href = '';
			if($link === '&id=532455') $href = 'https://www.yp.ru/detail/id/biznes_dialog_532455/';
			else if($link === '&id=2911171') $href = 'https://www.yp.ru/detail/id/biznes_dialog_2911171/';

			$stars = intval($elem->find('.review__body__data__header__rating-char', 0)->find('span[itemprop=ratingValue]', 0)->plaintext);
			if($stars >= 4){
				$data[] = [
					'service' => 'yp',
					'service_full' => 'Желтые страницы',
					'office_name' => $office_name,
					'link' => $href,
					'name' => trim($name_date[0]),
					'stars' => $stars,
					'review' => trim($elem->find('.review__comment', 0)->plaintext),
					'date' => $date,
					'timestamp' => russianDateToTimestamp(trim($date))
				];
			}
		}


		$page++;

	}

	return $data;
}

function getGoogle($link, $office_name){
	// google
	$json = file_get_contents('https://maps.googleapis.com/maps/api/place/details/json?fields=reviews&place_id=' . $link .'&key=AIzaSyDyPXgnfHbAnlwpGbkGkKCaGgsZl2g9DAE&language=ru');

	$json_data = json_decode($json);
	$data = [];

	$linkOnMap = '';
	if($link === 'ChIJsUTSK1owlkYRSUnmeOi6h_s') $linkOnMap = 'https://www.google.com/maps/place/%C2%AB%D0%91%D0%B8%D0%B7%D0%BD%D0%B5%D1%81+%D0%B4%D0%B8%D0%B0%D0%BB%D0%BE%D0%B3%C2%BB,+%D0%AE%D1%80%D0%B8%D0%B4%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%BE-%D0%B1%D1%83%D1%85%D0%B3%D0%B0%D0%BB%D1%82%D0%B5%D1%80%D1%81%D0%BA%D0%B0%D1%8F+%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F+(%D0%9E%D1%84%D0%B8%D1%81+%E2%84%9601+%C2%AB%D0%9C%D0%BE%D1%81%D0%BA%D0%BE%D0%B2%D1%81%D0%BA%D0%B8%D0%B5+%D0%B2%D0%BE%D1%80%D0%BE%D1%82%D0%B0%C2%BB)/@59.8916248,30.3141605,17z/data=!4m7!3m6!1s0x0:0xfb87bae878e64949!8m2!3d59.8916248!4d30.3163492!9m1!1b1';
	else if($link === 'ChIJfyWSauUxlkYRH2EsUzTcfWU') $linkOnMap = 'https://www.google.com/maps/place/%C2%AB%D0%91%D0%B8%D0%B7%D0%BD%D0%B5%D1%81+%D0%B4%D0%B8%D0%B0%D0%BB%D0%BE%D0%B3%C2%BB,+%D0%AE%D1%80%D0%B8%D0%B4%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%BE-%D0%B1%D1%83%D1%85%D0%B3%D0%B0%D0%BB%D1%82%D0%B5%D1%80%D1%81%D0%BA%D0%B0%D1%8F+%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F+(%D0%9E%D1%84%D0%B8%D1%81+%E2%84%9602+%C2%AB%D0%9F%D0%BB%D0%BE%D1%89%D0%B0%D0%B4%D1%8C+%D0%9B%D0%B5%D0%BD%D0%B8%D0%BD%D0%B0%C2%BB)/@59.9555457,30.3523856,17z/data=!3m1!4b1!4m5!3m4!1s0x0:0x657ddc34532c611f!8m2!3d59.9555457!4d30.3545743';
	else if($link === 'ChIJXQRIGf0xlkYRudSdf6oxBP4') $linkOnMap = 'https://www.google.com/maps/place/%C2%AB%D0%91%D0%B8%D0%B7%D0%BD%D0%B5%D1%81+%D0%B4%D0%B8%D0%B0%D0%BB%D0%BE%D0%B3%C2%BB,+%D0%AE%D1%80%D0%B8%D0%B4%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%BE-%D0%B1%D1%83%D1%85%D0%B3%D0%B0%D0%BB%D1%82%D0%B5%D1%80%D1%81%D0%BA%D0%B0%D1%8F+%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F+(%D0%9E%D1%84%D0%B8%D1%81+%E2%84%9603+%C2%AB%D0%9B%D0%B0%D0%B4%D0%BE%D0%B6%D1%81%D0%BA%D0%B0%D1%8F%C2%BB)/@59.9338347,30.432671,17z/data=!4m7!3m6!1s0x0:0xfe0431aa7f9dd4b9!8m2!3d59.9338347!4d30.4348597!9m1!1b1';
	else if($link === 'ChIJaRI2RCs940YRt-r8PQLl8VI') $linkOnMap = 'https://www.google.com/maps/place/%C2%AB%D0%91%D0%B8%D0%B7%D0%BD%D0%B5%D1%81+%D0%B4%D0%B8%D0%B0%D0%BB%D0%BE%D0%B3%C2%BB,+%D0%AE%D1%80%D0%B8%D0%B4%D0%B8%D1%87%D0%B5%D1%81%D0%BA%D0%BE-%D0%B1%D1%83%D1%85%D0%B3%D0%B0%D0%BB%D1%82%D0%B5%D1%80%D1%81%D0%BA%D0%B0%D1%8F+%D0%BA%D0%BE%D0%BC%D0%BF%D0%B0%D0%BD%D0%B8%D1%8F+(%D0%9E%D1%84%D0%B8%D1%81+%E2%84%9604+%C2%AB%D0%9A%D0%B0%D0%BB%D0%B8%D0%BD%D0%B8%D0%BD%D0%B3%D1%80%D0%B0%D0%B4%C2%BB)/@54.7123771,20.4742979,14z/data=!4m6!3m5!1s0x46e33d2b44361269:0x52f1e5023dfceab7!8m2!3d54.7225197!4d20.467916!15sCirQsdC40LfQvdC10YEg0LTQuNCw0LvQvtCzINC60LDQvNC10L3QvdCw0Y-SAQ5sZWdhbF9zZXJ2aWNlcw?shorturl=1';

	if(isset($json_data->result->reviews)){
		foreach($json_data->result->reviews as $review){
			if($review->rating >= 4){
				$data[] = [
					'service' => 'google',
					'service_full' => 'Google Maps',
					'office_name' => $office_name,
					'link' => $linkOnMap,
					'name' => $review->author_name,
					'stars' => $review->rating,
					'review' => $review->text,
					'date' => $review->time,
					'timestamp' => $review->time
				];
			}
		}

	}
	
	return $data;

}




$data = [];

$linksToParse = [
	[
		'file_name' => 'ofis1.json', 
		'office_name' => 'Офис №01 «Московские ворота»',
		'links' => [
			'https://2gis.ru/spb/firm/5348552839950730/tab/reviews',
			'https://yandex.ru/maps/org/biznes_dialog/1147862987/reviews/?ll=30.316120%2C59.891671&z=13',
			'ChIJsUTSK1owlkYRSUnmeOi6h_s',
			'&id=532455'

		]
	],
	[
		'file_name' => 'ofis2.json', 
		'office_name' => 'Офис №02 «Площадь Ленина»',
		'links' => [
			'https://2gis.ru/spb/firm/70000001017959061/tab/reviews',
			'https://yandex.ru/maps/org/biznes_dialog/141667628341/reviews/?ll=30.354677%2C59.955618&z=17',
			'ChIJfyWSauUxlkYRH2EsUzTcfWU',
		]
	],
	[
		'file_name' => 'ofis3.json', 
		'office_name' => 'Офис №03 «Ладожская»',
		'links' => [
			'https://2gis.ru/spb/firm/70000001017959061/tab/reviews',
			'https://yandex.ru/maps/org/biznes_dialog/1456236491/reviews/?ll=30.436284%2C59.934483&z=133',
			'ChIJXQRIGf0xlkYRudSdf6oxBP4',

		]
	],
	[
		'file_name' => 'ofis4.json', 
		'office_name' => 'Офис №04 «Калининград» в Калининграде',
		'links' => [
			'&id=2911171',
			'ChIJaRI2RCs940YRt-r8PQLl8VI'
		]
	],
];

$data = [];


foreach($linksToParse as $links){
	$items = [];
	$office_name = $links['office_name'];

	foreach($links['links'] as $link){
		if(strpos($link, '2gis.ru') !== false) $items = array_merge(get2Gis($link, $office_name), $items); 
		else if(strpos($link, 'yandex.ru') !== false) $items = array_merge(getYandex($link, $office_name), $items);
		else if(strpos($link, '&id=') !== false) $items = array_merge(getYa($link, $office_name), $items); 
		else $items = array_merge(getGoogle($link, $office_name), $items); 
	}



	if($items){

		$timestamps = [];
		foreach ($items as $key => $node) {
		   $timestamps[$key]    = $node['timestamp'];
		}
		array_multisort($timestamps, SORT_DESC, $items);

		$data[$links['file_name']] = $items;
		file_put_contents('./data/' . $links['file_name'], json_encode($data[$links['file_name']]));

	}
}


if($data){
	$allData = [];

	foreach($linksToParse as $links){
		if(isset($data[$links['file_name']])){
			$allData = array_merge($allData, $data[$links['file_name']]);
		}
	}

	$timestamps = [];
	foreach ($allData as $key => $node) {
	   $timestamps[$key]    = $node['timestamp'];
	}
	array_multisort($timestamps, SORT_DESC, $allData);

	file_put_contents('./data/all.json', json_encode($allData));

	echo "Executed.";
}