<?php 
$e = &$modx->Event;

function num_declension ($number, $titles) {
	$abs = abs($number);
	$cases = array (2, 0, 1, 1, 1, 2);
	return $number . " ". $titles[ ($abs%100 > 4 && $abs %100 < 20) ? 2 : $cases[min($abs%10, 5)] ];
}

switch($e->name){
	case 'OnManagerWelcomeHome':
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

		$context = stream_context_create(array(
			'http' => array(
				'method' => 'GET',
				'header' => 'Authorization: OAuth ' . $token . PHP_EOL.
				'Content-Type: application/x-yametrika+json' . PHP_EOL
			),
		));

		$url = 'https://api-metrika.yandex.net/stat/v1/data';
		$y_params = [
			'ids'         => $counter_id,
			'oauth_token' => $token,
			'metrics'     => 'ym:s:visits,ym:s:pageviews,ym:s:users',
			'dimensions'  => 'ym:s:date',
			'date1'       => $days . 'daysAgo',
			'date2'       => 'yesterday',
			'sort'        => 'ym:s:date',
		];
		$json = json_decode(file_get_contents( $url . '?' . http_build_query($y_params), false, $context), true);
		$data = $json['data'];

		$tmpdata = [];
		foreach($data as $item) {
			$tmpdata['pageviews'][]  = $item['metrics'][1];
			$tmpdata['visits'][]     = $item['metrics'][0];
			$tmpdata['users'][]      = $item['metrics'][2];
			$tmpdata['categories'][] = $item['dimensions'][0]['name'];
		}
		$categories = json_encode($tmpdata['categories'], JSON_UNESCAPED_UNICODE);
		$series = json_encode([
			[
				'name' => 'Просмотры',
				'indicator' => '',
				'data' => $tmpdata['pageviews'],
				'animation'=> [
					'duration' => $duration ? $duration + 3000 : 0,
					'easing' => 'ease'
				]
			],
			[
				'name' => 'Визиты',
				'indicator' => '',
				'data' => $tmpdata['visits'],
				'animation'=> [
					'duration' => $duration ? $duration + 2000 : 0,
					'easing' => 'ease'
				]
			],
			[
				'name' => 'Посетители',
				'indicator' => '',
				'data' => $tmpdata['users'],
				'animation'=> [
					'duration' => $duration ? $duration + 1000 : 0,
					'easing' => 'ease'
				]
			]
		], JSON_UNESCAPED_UNICODE);
		$metrika_content .= '<script src="/assets/plugins/dashboard_widgets/yandex_metrika_graph/highcharts.js"></script>';
		$metrika_content .=  '<div id="container"></div>';
		$metrika_content .= "<style>
.highcharts-tooltip table.table {
	font-size: 1.2em;
}
.highcharts-tooltip table.table thead th {
	font-weight: bold;
	font-size: 1em;
	border-top: 0;
}
.highcharts-tooltip table.table thead th:last-child,
.highcharts-tooltip table.table thead td:last-child,
.highcharts-tooltip table.table tbody th:last-child,
.highcharts-tooltip table.table tbody td:last-child {
	padding-right: .15rem !important;
	font-weight: bold;
}
.highcharts-tooltip table.table tbody th:last-child,
.highcharts-tooltip table.table tbody td:last-child {
	min-width: 25px;
}
.highcharts-tooltip table.table tbody td:first-child {
	padding-left: .15rem !important;
	text-align: center;
	line-height: 1;
}
.highcharts-tooltip table.table tbody td:first-child span.circle,
.highcharts-tooltip table.table tbody td:first-child span.diamond,
.highcharts-tooltip table.table tbody td:first-child span.square {
	display: block;
	overflow: hidden;
	width: 10px;
	height: 10px;
	transform-origin: center center;
}
.highcharts-tooltip table.table tbody td:first-child span.circle {
	border-radius: 50%;
}
.highcharts-tooltip table.table tbody td:first-child span.diamond {
	transform: rotate(45deg);
}
.highcharts-tooltip table.table tbody td:first-child span.square {
	transform: rotate(0);
}
</style>
<script>
!(function(charts){
	charts.chart('container', {
		chart: {
			type: 'spline'
		},
		title: {
			text: 'Активность посетителей за " . num_declension(count($tmpdata['categories']), array('день', 'дня', 'дней')) . "',
			x: -20
		},
		tooltip: {
			split: !0,
			distance: 30,
			padding: 10,
			useHTML: true,
			formatter : function() {
				if (typeof this.points != 'undefined') {
					let date = [...this.key.split('-')].reverse().join('.'),
						s = '<table class=\"table\"><thead><tr><th colspan=\"3\">' + date + '</th></tr></thead><tbody>',
						color;
					$.each(this.points, function(i, point) {
						color = point.series.color;
						s += '<tr><td><span class=\"' + point.series.symbol + '\" style=\"color:' + color + ';  background-color: ' + color + ';\"></span></td>';
						s += '<td>' + point.series.name + '</td><td class=\"text-right\">' + Highcharts.numberFormat(point.y, 0) + '</td></tr>';
					});
					s += '</tbody></table>';
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
</script>";    

		$widgets['yandex-metrika'] = array(
			'menuindex' => $params['menuindex'],
			'id' => 'yandex-metrika',
			'cols' => 'col-sm-' . $params['widget_width'],
			'icon' => 'fa-bar-chart',
			'title' => 'Метрика',
			'body' => '<div class="card-body">'.$metrika_content.'</div>'
		);
		$modx->event->output(serialize($widgets));
		break;
}