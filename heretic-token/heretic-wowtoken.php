<?php

/*
Plugin Name: World of Warcraft WoW Tokens
Plugin URI: http://hereticgaming.com
Description: World of Warcraft WoW Tokens - HereticGaming.com
Author: Canalla
Version: 1.0
Author URI: http://hereticgaming.com
*/

/*  Copyright 2016 Canalla (email: info at hereticgaming.com)
    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.
    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

class HereticTokens extends WP_Widget {

    private $urlImages;

	private $jsonfeed = 'https://wowtoken.info/wowtoken.json';

	private $refreshinterval = 600;

	private $_regions = array(
		'CN' => 'China',
		'EU' => 'Europe',
		'KR' => 'Korea',
		'NA' => 'North America',
		'TW' => 'Taiwan'
	);

	public function __construct() {
		parent::__construct('HereticTokens', 'World of Warcraft WoW Tokens', array('description' => 'HereticGaming.com - World of Warcraft WoW Tokens Widget'));
		$this->urlImages = WP_PLUGIN_URL.'/heretic-wowtoken/images';
	}

	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
			$region = $instance[ 'region' ];
		} 	else {
			$title =  'WoWToken.info';
			$region = 'EU';
		}
	?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
</p>
<p>
<label for="<?php echo $this->get_field_id( 'region' ); ?>"><?php _e( 'Choose Region : ' ); ?></label>
<select class="widefat" name="<?php echo $this->get_field_name('region'); ?>" id="">
<?php
	$output = '';
	foreach ($this->_regions as $option=>$value) {
		$selected = ($option == $instance['region']) ? ' selected' : '';
		$output .= '<option'.$selected.' value="'.$option.'">'.$value.'</option>'."\n";
	}
	echo $output;
?>
</select>
</p>
<p>
<label for="<?php echo $this->get_field_id( 'wtlink' ); ?>"><?php _e( 'Link WowToken.info : ' ); ?></label>
<select class="widefat" name="<?php echo $this->get_field_name('wtlink'); ?>" id="">
	<option <?php echo ($instance['wtlink'] == 'true') ? 'selected ' : ''; ?>value="true">Yes</option>
	<option <?php echo ($instance['wtlink'] == 'false') ? 'selected ' : ''; ?>value="false">No</option>
</select>
</p>
<p>
<label for="<?php echo $this->get_field_id( 'displaylist' ); ?>"><?php _e( 'Display as List : ' ); ?></label>
<select class="widefat" name="<?php echo $this->get_field_name('displaylist'); ?>" id="">
	<option <?php echo ($instance['displaylist'] == 'true') ? 'selected ' : ''; ?>value="true">Yes</option>
	<option <?php echo ($instance['displaylist'] == 'false') ? 'selected ' : ''; ?>value="false">No</option>
</select>
</p>
	<?php
	}

	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['region'] = ( ! empty( $new_instance['region'] ) ) ? strip_tags( $new_instance['region'] ) : '';
		$instance['wtlink'] = ( ! empty( $new_instance['wtlink'] ) ) ? strip_tags( $new_instance['wtlink'] ) : '';
		$instance['displaylist'] = ( ! empty( $new_instance['displaylist'] ) ) ? strip_tags( $new_instance['displaylist'] ) : '';
		return $instance;
	}

	public function widget( $args, $instance ) {
		$lasttime = get_option('heretictokendataTime') ? get_option('heretictokendataTime') : (current_time('timestamp') - 601); //901
		if ((current_time('timestamp') - $lasttime) > $this->refreshinterval) {
			$feedData = wp_remote_get($this->jsonfeed);
			if ($feedData['response']['code'] == 200) {
				$tmpData = json_decode($feedData['body']);
				$tmpData = $tmpData->update;
				$dataBuy = array(
					'CN' => $tmpData->{'CN'}->{'formatted'}->{'buy'},
					'EU' => $tmpData->{'EU'}->{'formatted'}->{'buy'},
					'KR' => $tmpData->{'KR'}->{'formatted'}->{'buy'},
					'NA' => $tmpData->{'NA'}->{'formatted'}->{'buy'},
					'TW' => $tmpData->{'TW'}->{'formatted'}->{'buy'}
				);
				$dataTimeSell = array(
					'CN' => $tmpData->{'CN'}->{'formatted'}->{'timeToSell'},
					'EU' => $tmpData->{'EU'}->{'formatted'}->{'timeToSell'},
					'KR' => $tmpData->{'KR'}->{'formatted'}->{'timeToSell'},
					'NA' => $tmpData->{'NA'}->{'formatted'}->{'timeToSell'},
					'TW' => $tmpData->{'TW'}->{'formatted'}->{'timeToSell'}
				);
				$dataMax = array(
					'CN' => $tmpData->{'CN'}->{'formatted'}->{'24max'},
					'EU' => $tmpData->{'EU'}->{'formatted'}->{'24max'},
					'KR' => $tmpData->{'KR'}->{'formatted'}->{'24max'},
					'NA' => $tmpData->{'NA'}->{'formatted'}->{'24max'},
					'TW' => $tmpData->{'TW'}->{'formatted'}->{'24max'}
				);
				$dataMin = array(
					'CN' => $tmpData->{'CN'}->{'formatted'}->{'24min'},
					'EU' => $tmpData->{'EU'}->{'formatted'}->{'24min'},
					'KR' => $tmpData->{'KR'}->{'formatted'}->{'24min'},
					'NA' => $tmpData->{'NA'}->{'formatted'}->{'24min'},
					'TW' => $tmpData->{'TW'}->{'formatted'}->{'24min'}
				);
				update_option('heretictokendataBuy', json_encode($dataBuy));
				update_option('heretictokendataTimeSell', json_encode($dataTimeSell));
				update_option('heretictokendataMax', json_encode($dataMax));
				update_option('heretictokendataMin', json_encode($dataMin));
				update_option('heretictokendataTime', current_time('timestamp'));
			} else {
				$dataBuy = json_decode(get_option('heretictokendataBuy'), true);
				$dataTimeSell = json_decode(get_option('heretictokendataTimeSell'), true);
				$dataMax = json_decode(get_option('heretictokendataMax'), true);
				$dataMin = json_decode(get_option('heretictokendataMin'), true);
			}
		} else {
				$dataBuy = json_decode(get_option('heretictokendataBuy'), true);
				$dataTimeSell = json_decode(get_option('heretictokendataTimeSell'), true);
				$dataMax = json_decode(get_option('heretictokendataMax'), true);
				$dataMin = json_decode(get_option('heretictokendataMin'), true);
		}
		$title = apply_filters( 'widget_title', $instance['title'] );
		echo $args['before_widget'];
		if ( ! empty( $title ) )
		echo $args['before_title'] . $title . $args['after_title'];
		$regionPrice = $dataBuy[$instance['region']];
		$regionTimeSell = $dataTimeSell[$instance['region']];
		$regionMax = $dataMax[$instance['region']];
		$regionMin = $dataMin[$instance['region']];
		if ($instance['displaylist'] == 'true') {
			echo '<ul>';
			echo '<li>Region: '.$this->_regions[$instance['region']].'</li>';
			echo '<li>Current Price: <img src="' .$this->urlImages.'/goldicon.png'. '" > '.$regionPrice.'</li>';
			echo '<li>Time to Sell: '.$regionTimeSell.'</li>';
			echo '<li>24 Hour Range: '.$regionMin.' to '.$regionMax.' </li>';
			echo '</ul>';
		}
		else {
			echo '<div>Region: '.$this->_regions[$instance['region']].'</div>';
			echo '<div>Current Price: <img src="' .$this->urlImages.'/goldicon.png'. '" > '.$regionPrice.'</div>';
			echo '<div>Time to Sell: '.$regionTimeSell.'</div>';
			echo '<div>24 Hour Range: '.$regionMin.' to '.$regionMax.' </div>';
		}
		if ((current_time('timestamp') - get_option('heretictokendataTime')) > 0) {
			$ago = current_time('timestamp') - get_option('heretictokendataTime');
			$agomin = (date(i, $ago) > 0) ? ltrim(date(i, $ago), 0) .' min ' : '';
			$agosec = (date(s, $ago) > 0) ? ltrim(date(s, $ago), 0) .' sec ' : '';
			$ago = 'Updated '.$agomin.$agosec.'ago';
		} else {
			$ago = 'Just updated';
		}
		if ($instance['wtlink'] == 'true') {
			$wtlink = '<a href="http://www.wowtoken.info" target="_blank">WowToken.info</a>';
		} else {
			$wtlink = 'WowToken.info';
		}
		echo '<div>'.$ago.', via '.$wtlink.'</p></div>';
		echo $args['after_widget'];
	}	
}

add_action( 'widgets_init', function() {
	register_widget( 'HereticTokens' );
});

add_action('activated_plugin', function() {
	$feedData = wp_remote_get('https://wowtoken.info/wowtoken.json');
	if ($feedData['response']['code'] == 200) {
		$tmpData = json_decode($feedData['body']);
		$tmpData = $tmpData->update;
		$dataBuy = array(
			'CN' => $tmpData->{'CN'}->{'formatted'}->{'buy'},
			'EU' => $tmpData->{'EU'}->{'formatted'}->{'buy'},
			'KR' => $tmpData->{'KR'}->{'formatted'}->{'buy'},
			'NA' => $tmpData->{'NA'}->{'formatted'}->{'buy'},
			'TW' => $tmpData->{'TW'}->{'formatted'}->{'buy'}
		);
		$dataTimeSell = array(
			'CN' => $tmpData->{'CN'}->{'formatted'}->{'timeToSell'},
			'EU' => $tmpData->{'EU'}->{'formatted'}->{'timeToSell'},
			'KR' => $tmpData->{'KR'}->{'formatted'}->{'timeToSell'},
			'NA' => $tmpData->{'NA'}->{'formatted'}->{'timeToSell'},
			'TW' => $tmpData->{'TW'}->{'formatted'}->{'timeToSell'}
		);
		$dataMax = array(
			'CN' => $tmpData->{'CN'}->{'formatted'}->{'24max'},
			'EU' => $tmpData->{'EU'}->{'formatted'}->{'24max'},
			'KR' => $tmpData->{'KR'}->{'formatted'}->{'24max'},
			'NA' => $tmpData->{'NA'}->{'formatted'}->{'24max'},
			'TW' => $tmpData->{'TW'}->{'formatted'}->{'24max'}
		);
		$dataMin = array(
			'CN' => $tmpData->{'CN'}->{'formatted'}->{'24min'},
			'EU' => $tmpData->{'EU'}->{'formatted'}->{'24min'},
			'KR' => $tmpData->{'KR'}->{'formatted'}->{'24min'},
			'NA' => $tmpData->{'NA'}->{'formatted'}->{'24min'},
			'TW' => $tmpData->{'TW'}->{'formatted'}->{'24min'}
		);
	} else {
		$dataBuy = array(
			'CN' => '0',
			'EU' => '0',
			'KR' => '0',
			'NA' => '0',
			'TW' => '0'
		);
		$dataTimeSell = array(
			'CN' => '0',
			'EU' => '0',
			'KR' => '0',
			'NA' => '0',
			'TW' => '0'
		);
		$dataMax = array(
			'CN' => '0',
			'EU' => '0',
			'KR' => '0',
			'NA' => '0',
			'TW' => '0'
		);
		$dataMix = array(
			'CN' => '0',
			'EU' => '0',
			'KR' => '0',
			'NA' => '0',
			'TW' => '0'
		);
	}

	update_option('heretictokendataBuy', json_encode($dataBuy));
	update_option('heretictokendataTimeSell', json_encode($dataTimeSell));
	update_option('heretictokendataMax', json_encode($dataMax));
	update_option('heretictokendataMin', json_encode($dataMin));
	update_option('heretictokendataTime', current_time('timestamp'));
});

add_action('deactivated_plugin', function() {
	delete_option('heretictokendataBuy');
	delete_option('heretictokendataTimeSell');
	delete_option('heretictokendataMax');
	delete_option('heretictokendataMin');
	delete_option('heretictokendataTime');
});
?>