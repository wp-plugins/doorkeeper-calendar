<?php


/*
Plugin Name: Doorkeeper Calendar
Plugin URI: http://unplat.info/
Description: Doorkeeperから指定のグループのイベントカレンダーを表示します。1)Doorkeeper設定より、DoorkeeperのグループIDを設定。2)表示したい箇所にecho $dkCalendar->get_calendar();と記述。
Version: 0.1
Author: Ippei Sumida
Author URI: http://unplat.info/
License: GPL V2
*/
class DoorkeeperCalendar {

    /**
     * @var string Doorkeeper APIのURL
     */
    var $url;


    /**
     * コンストラクタ
     */
    public function __construct() {
		$this->url = "http://api.doorkeeper.jp/groups/%s/events";
        add_action('admin_menu', array($this, 'add_pages'));
        add_action('wp_enqueue_scripts', array($this, 'calendar_enqueue_style'));
        add_action('wp_enqueue_scripts', array($this, 'calendar_enqueue_script'));
	}


    /**
     * styleタグの設定
     */
    public function calendar_enqueue_style() {
        $dir = plugin_dir_url(__FILE__);
        wp_enqueue_style("fullcalendar.css", $dir . "css/fullcalendar.min.css");
    }

    /**
     * scriptタグの設定
     */
    public function calendar_enqueue_script() {
        $dir = plugin_dir_url(__FILE__);
        wp_enqueue_script("moment-with-locales.js", $dir . "js/moment-with-locales.js", array("jquery"));
        wp_enqueue_script("fullcalendar.js", $dir . "js/fullcalendar.min.js", array("moment-with-locales.js"));
    }

    /**
     * DoorkeeperAPIのURLを返す
     * @return string
     */
    public function get_dk_url() {
        $opt = get_option('dk_group_id');
        if (! isset($opt['text'])) {
            return "";
        }
        $dkUrl = sprintf($this->url, $opt["text"]);
        return $dkUrl;
    }

    /**
     * 管理画面の設定
     */
    public function add_pages() {
        add_menu_page('Doorkeeper設定', 'Doorkeeper設定', 'level_8', __FILE__, array($this, 'show_option_page'), '', 26);
    }

    /**
     * 管理画面での設定画面の表示・データの保存
     */
    public function show_option_page() {
        if (isset($_POST["dk_group_id"])) {
            check_admin_referer('shoptions');
            $opt = $_POST["dk_group_id"];
            update_option('dk_group_id', $opt);
            ?><div class="updated fade"><p><strong><?php _e('保存しました。'); ?></strong></p></div><?php
        }
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br /></div><h2>グループID設定</h2>
            <form action="" method="post">
                <?php
                wp_nonce_field('shoptions');
                $opt = get_option('dk_group_id');
                $show_text = isset($opt["text"]) ? $opt["text"] : null;
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="inputtext">グループID</label>
                        </th>
                        <td><input name="dk_group_id[text]" type="text" id="inputtext" value="<?php echo $show_text ?>" class="regular-text" /></td>
                    </tr>
                </table>
                <p class="submit"><input type="submit" name="Submit" class="button-primary" value="変更を保存" /></p>
            </form>
        <!-- /.wrap --></div>
        <?php
    }

    /**
     * 設定されたグループIDのイベントをFullCalendar用に整形したJSONを返す
     * @return string
     */
    public function get_json() {
        $opt = get_option('dk_group_id');
        if (! isset($opt['text'])) {
            return "";
        }
        $dkUrl = $this->get_dk_url();
        $curl = curl_init($dkUrl);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($curl);
        curl_close($curl);
        $events = array();
        if ($response) {
            $json = json_decode($response, true);
            if (is_array($json)) {
                foreach ($json as $row) {
                    $event = $row["event"];
                    $event["public_url"] = preg_replace("/^https:/", "http:", $event["public_url"]);
                    $data = array(
                        "title" => $event["title"],
                        "start" => $this->getLocalDate( $event["starts_at"] ),
                        "end" => $this->getLocalDate( $event["ends_at"] ),
                        "url" => $event["public_url"]
                    );
                    $events[] = $data;
                }
                return (json_encode($events));
            }
        }
        return "";
    }

    /**
     * 日付を日本のロケールに変更
     * @param $date
     * @return string
     */
    function getLocalDate( $date ){

        $localDate = new DateTime($date);
        $localDate->setTimeZone(new DateTimeZone('Asia/Tokyo'));

        return $localDate->format('Y-m-d H:i:s');
    }

    /**
     * カレンダー取得
     */
	public function get_calendar() {
        $json = $this->get_json();
        if ($json == "") {
            return "<script>console.log('指定のGroupIDのイベントデータが取得できませんでした。');</script>";
        }
        $text = <<< EOT
<script>
jQuery(document).ready(function() {
    jQuery("#dk_calendar").fullCalendar({
        header : {
            left: "prev,next today",
            center: "title",
            right: "month"
        },
        firstDay: 1,
        lang: "ja",
        editable: false,
        eventLimit: true,
        timeFormat: "H:mm〜",
        events: $json,
        eventClick: function(calEvent, jsEvent, view) {
            window.open(calEvent.url);
            return false;
        }
    });
});
</script>
<div id="dk_calendar"></div>
EOT;
        return $text;
	}



}

$dkCalendar = new DoorkeeperCalendar();
