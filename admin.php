<?php
function EXIF_admin_load() {
    global $db_prefix, $suri, $pluginURL;

    $entry = isset($_GET['entry']) ? intval($_GET['entry']) : 0;
    $p = isset($_GET['p']) ? intval($_GET['p']) : 1;

    $ctx = Model_Context::getInstance();
    $db_prefix = $ctx->getProperty('database.prefix');
    $db = DBModel::getInstance();
    $db->reset('ExifCaches');

    $entries = array();
    $count = $db->getCount();
    $all = $db->getAll('entry_id');
    foreach($all as $key => list($k, $v)) {
        array_push($entries, $k);
    }
    $entries = array_unique($entries);

    $items = array();
    $start = 15 * ($p - 1);
    $db->reset('ExifCaches');
    if($entry !== 0 && in_array($entry, $entries) !== false) {
        $db->setQualifier('entry_id', 'eq', $entry, true);
    }
    $total = $db->getCount();
    $db->setLimit(15, $start);
    $items = $db->getAll();
    $totalp = ceil($total / 15); // 15 items per page

    $entries = array_map(function($value) {
        global $db_prefix;

        $title = '';
        $article = POD::queryRow("SELECT `title` FROM `{$db_prefix}Entries` WHERE " .
            "`id` = {$value}");
        if(!is_null($article) && $article['title']) {
            $title = $article['title'];
        }
        return array($value, $title);
    }, $entries);

?>
<form method="get" action="<?php echo $suri['url'] ?>">
    <div>
        <input type="hidden" name="name" value="<?php echo $_GET['name'] ?>">
        Select entry <select name="entry"><?php
            echo '<option value="0">all</option>';
            foreach($entries as $key => list($num, $title)) {
                $title = htmlspecialchars($title);
                $selected = $entry == $num ? ' selected' : '';
                echo '<option value="' . $num . '"' . $selected . '>' . $num . ' :: ' . $title . '</option>';
            }
        ?></select>
        <input type="submit" value="go">
    </div>
</form>

<table class="data-inbox" cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th><span class="text">ENTRY</span></th>
            <th><span class="text">TYPE</span></th>
            <th><span class="text">PREVIEW</span></th>
            <th><span class="text">DATA</span></th>
            <th><span class="text">ENABLED</span></th>
            <th><span class="text">DELETE</span></th>
        </tr>
    </thead>
    <tbody>
    <?php
        foreach($items as $idx => $item) {
            $preview = '';
            $data = json_decode($item['data'], true);
            if(array_key_exists('NoEXIF', $data)) {
                $preview = 'NO EXIF';
            } else {
                $preview = "{$data['Make']} {$data['Model']}";
            }

            $preview_image = $item['url'];
            if($item['type'] == 0) {
                $preview_image = substr($item['url'], stripos($item['url'], 'attach') - 1);
            }

            $data_tooltip = json_encode($data, JSON_PRETTY_PRINT);
            $data_tooltip = strtr($data_tooltip, array(
                "\r" => '',
                "\n" => '<br>',
                "\/" => '/',
                '"' => "",
                '    ' => '&nbsp;&nbsp;&nbsp;&nbsp;<strong>',
                ': ' => '</strong> ',
                ',' => ''
            ));
    ?>
        <tr>
            <td><?php echo $item['entry_id'] ?></td>
            <td><?php echo $item['type'] == 0 ? 'blog' : 'other' ?></td>
            <td><a href="<?php echo $preview_image ?>" target="_blank" class="preview"><img src="<?php echo $preview_image ?>" alt="preview image" width="auto" height="20px"></a></td>
            <td><a class="tooltip" title="<?php echo $data_tooltip ?>"><?php echo $preview ?></a></td>
            <td><?php echo $item['enabled'] == 1 ? 'O' : 'X' ?></td>
            <td><?php  ?></td>
        </tr>
    <?
        }
    ?>
    </tbody>
</table>

<div class="data-subbox">
    <div id="page-section" class="section">
        <form method="get" action="<?php echo $suri['url'] ?>">
            <div>
                <input type="hidden" name="name" value="<?php echo $_GET['name'] ?>">
                <input type="hidden" name="entry" value="<?php echo $entry ?>">
                Page <select name="p" onchange="this.form.submit()">
                    <?php
                    for($i = 1; $i <= $totalp; $i++) {
                        $selected = $i == $p ? ' selected' : '';
                        echo '<option value="' . $i . '"'. $selected .'>' . $i . '</option>';
                    }
                    ?>
                </select> of <?php echo $totalp ?>
            </div>
        </form>
    </div>
</div>

<script src="<?php echo $pluginURL ?>/images/admin.js"></script>
<?
}
?>