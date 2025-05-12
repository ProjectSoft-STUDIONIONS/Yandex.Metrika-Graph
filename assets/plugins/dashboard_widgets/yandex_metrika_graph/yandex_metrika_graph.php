<?php 
$e = &$modx->Event;

function num_declension ($number, $titles) {
	$abs = abs($number);
	$cases = array (2, 0, 1, 1, 1, 2);
	return $number . " ". $titles[ ($abs%100 > 4 && $abs %100 < 20) ? 2 : $cases[min($abs%10, 5)] ];
}

switch($e->name){
	case 'OnManagerWelcomeHome':

		function metrika_curl($url, $token = "")
		{
			$headers = array(
				'Authorization: OAuth ' . $token,
				'Content-Type: application/x-yametrika+json'
			);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/133.0.0.0 Safari/537.36 OPR/118.0.0.0');
			$data = curl_exec($ch);
			$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			if((int)$httpCode > 399) {
				$data = false;
			}
			
			curl_close($ch);
			return $data;
		}

		$id = $params['app_id'];
		$token = $params['app_token'];
		$counter_id = $params['counter_id'];

		$duration = intval($params['duration']) * 1000;
		$days = intval($params['days']);
		
		if($params['show_dev_links']){
			$metrika_content .= '<a href="https://oauth.yandex.ru/client/new" target="_blank">Создать приложение</a>';
			$metrika_content .= '<BR>';
			$metrika_content .= '<a href="https://oauth.yandex.ru/authorize?response_type=token&client_id=' . $id . '" target="_blank">Получить доступ к счётчику</a>';
			$metrika_content .= '<BR>';
		}

		$y_params = [
			'ids'         => $counter_id,
			'oauth_token' => $token,
			'metrics'     => 'ym:s:visits,ym:s:pageviews,ym:s:users',
			'dimensions'  => 'ym:s:date',
			'date1'       => $days . 'daysAgo',
			'date2'       => 'yesterday',
			'sort'        => 'ym:s:date',
		];

		$url = 'https://api-metrika.yandex.net/stat/v1/data?' . http_build_query($y_params);

		$get_metrika = metrika_curl($url, $token);
		if($get_metrika){
			$json = json_decode($get_metrika, true);
			// Можно не проверять, но всё-таки исключим ошибки
			if(!$json) {
				$json = array();
				$json['data'] = array();
			}
		}else{
			$json = array();
			$json['data'] = array();
		}

		$data = $json['data'];

		$tmpdata = array(
			'pageviews' => array(),
			'visits' => array(),
			'users' => array(),
			'categories' => array(),
		);
		foreach($data as $item) {
			$str = implode(".", array_reverse(explode('-', $item['dimensions'][0]['name'])));
			$tmpdata['pageviews'][]  = $item['metrics'][1];
			$tmpdata['visits'][]     = $item['metrics'][0];
			$tmpdata['users'][]      = $item['metrics'][2];
			$tmpdata['categories'][] = $str;
		}

		$categories = json_encode($tmpdata['categories'], JSON_UNESCAPED_UNICODE);
		$series = json_encode([
			[
				'name' => 'Посетители',
				'data' => $tmpdata['users'],
				'animation'=> [
					'duration' => $duration ? $duration + 1000 : 0,
					'easing' => 'ease'
				]
			],
			[
				'name' => 'Визиты',
				'data' => $tmpdata['visits'],
				'animation'=> [
					'duration' => $duration ? $duration + 2000 : 0,
					'easing' => 'ease'
				]
			],
			[
				'name' => 'Просмотры',
				'data' => $tmpdata['pageviews'],
				'animation'=> [
					'duration' => $duration ? $duration + 3000 : 0,
					'easing' => 'ease'
				]
			]
		], JSON_UNESCAPED_UNICODE);
		$metrika_content .= '<script src="/assets/plugins/dashboard_widgets/yandex_metrika_graph/highcharts.js"></script>' . PHP_EOL;
		$metrika_content .=  '<div id="container"></div>' . PHP_EOL;
		$metrika_content .= "<style>
	.highcharts-tooltip table.table {
		font-size: 1.2em;
	}
	.highcharts-tooltip table.table thead > tr > th {
		font-weight: bold;
		font-size: 1em;
		border-top: 0;
		padding-left: .15rem !important;
		padding-right: .15rem !important;
	}
	.highcharts-tooltip table.table > thead > tr > th > span {
		font-weight: normal;
	}
	.highcharts-tooltip table.table > thead > tr > th:first-child,
	.highcharts-tooltip table.table > thead > tr > td:first-child {
		padding-left: .15rem !important;
	}
	.highcharts-tooltip table.table > thead > tr > th:last-child,
	.highcharts-tooltip table.table > thead > tr > td:last-child,
	.highcharts-tooltip table.table > tbody > tr > th:last-child,
	.highcharts-tooltip table.table > tbody > tr > td:last-child {
		padding-right: .15rem !important;
		font-weight: bold;
	}
	.highcharts-tooltip table.table > tbody > tr > th:last-child,
	.highcharts-tooltip table.table > tbody > tr > td:last-child {
		min-width: 25px;
	}
	.highcharts-tooltip table.table > tbody > tr > td:first-child {
		padding-left: .15rem !important;
		text-align: center;
		line-height: 1;
	}
	.highcharts-tooltip table.table > tbody > tr > td:first-child span.circle,
	.highcharts-tooltip table.table > tbody > tr > td:first-child span.diamond,
	.highcharts-tooltip table.table > tbody > tr > td:first-child span.square {
		display: block;
		overflow: hidden;
		width: 10px;
		height: 10px;
		transform-origin: center center;
	}
	.highcharts-tooltip table.table > tbody > tr > td:first-child span.circle {
		border-radius: 50%;
	}
	.highcharts-tooltip table.table > tbody > tr > td:first-child span.diamond {
		transform: rotate(45deg);
	}
	.highcharts-tooltip table.table > tbody > tr > td:first-child span.square {
		transform: rotate(0);
	}
</style>
<script>
	!(function(charts){
		charts.chart('container', {
			chart: {
				type: 'spline'
			},
			credits: {
				enabled: !1
			},
			title: {
				text: 'Активность посетителей за " . num_declension(count($tmpdata['categories']), array('день', 'дня', 'дней')) . "',
				x: -20
			},
			colors: [ \"#00e272\", \"#2caffe\", \"#544fc5\"],
			tooltip: {
				split: !0,
				distance: 30,
				padding: 10,
				useHTML: true,
				formatter : function() {
					if (typeof this.points != 'undefined') {
						let date = this.key, s = `<table class=\"table\"><thead><tr><th colspan=\"3\"><span>Дата:</span>&nbsp;\${date}</th></tr></thead><tbody>`, color, symbol, name, views;
						$.each(this.points, function(i, point) {
							color = point.series.color;
							symbol = point.series.symbol;
							name = point.series.name;
							views = Highcharts.numberFormat(point.y, 0);
							s += `<tr><td><span class=\"\${symbol}\" style=\"background-color:\${color};\"></span></td><td>\${name}</td><td class=\"text-right\">\${views}</td></tr>`;
						});
						s += `</tbody></table>`;
						return s;
					}
					return '';
				},
			},
			xAxis: {
				categories: $categories,
				crosshair: {
					enabled: true
				}
			},
			yAxis: {
				title: {
					text: 'Количество'
				},
			},
			legend: {
				layout: 'vertical',
				align: 'right',
				verticalAlign: 'middle',
				borderWidth: 0
			},
			series: $series,
			accessibility: {
				enabled: false
			}
		});
	})(Highcharts);
</script>" . PHP_EOL;    

		$widgets['yandex-metrika'] = array(
			'menuindex' => $params['menuindex'],
			'id' => 'yandex-metrika',
			'cols' => 'col-sm-' . $params['widget_width'],
			'icon' => 'fa-bar-chart',
			'title' => 'Метрика',
			'body' => '<div class="card-body">' . $metrika_content . '</div>'
		);
		$modx->event->output(serialize($widgets));
		break;
}