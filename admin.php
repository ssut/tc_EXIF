<?php
function EXIF_admin_load() {
    global $db_prefix, $suri, $defaultURL, $pluginURL;

    $category = isset($_GET['category']) ? intval($_GET['category']) : 0;
    $entry = isset($_GET['entry']) ? intval($_GET['entry']) : 0;
    $p = isset($_GET['p']) ? intval($_GET['p']) : 1;

    $ctx = Model_Context::getInstance();
    $db_prefix = $ctx->getProperty('database.prefix');
    $db = DBModel::getInstance();
    $db->reset('ExifCaches');

    $entries = array();
    $count = $db->getCount();
    $db->setOrder('entry_id', 'desc');
    $all = $db->getAll('entry_id');
    $all = array_values($all);
    reset($all);
    while(list($key, $k) = each($all)) {
        array_push($entries, $k[0]);
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

    $categories = POD::queryAll("SELECT `blogid`, `id`, `name` FROM `{$db_prefix}Categories`");
    $db->reset('ExifDisabledCategories');
    $off_categories = $db->getAll();
?>
<form id="toggleCategory" method="post" action="<?php $defaultURL ?>/plugin/EXIF/saveToggle">
    <table class="data-inbox" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th><span class="text">Enabled categories</span></th>
            </tr>
        </thead>
        <tbody>
            <tr class="even-line inactive-class">
                <td>
                    <div class="categoryList">
                        <div class="content">
                        <?php
                            foreach($categories as $category) {
                                $disabled = false;
                                foreach($off_categories as $off_category) {
                                    if($off_category['blog_id'] == $category['blogid'] &&
                                        $off_category['category_id'] == $category['id']) {
                                        $disabled = true;
                                        break;
                                    }
                                }
                                $name = $category['name'];
                                $value = $category['blogid'] . ',' . $category['id'];
                                $checked = $disabled ? '' : ' checked';
                                echo '<label>';
                                echo ' <input type="checkbox" name="on[]" ' . $checked . ' value="' . $value . '"> ' . $name;
                                echo '</label>';
                            }
                        ?>
                        </div>
                        <input type="submit" value="save">
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</form>
<form id="listFilter" method="get" action="<?php echo $suri['url'] ?>">
    <input type="hidden" name="name" value="<?php echo $_GET['name'] ?>">
    <table class="data-inbox" cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th><span class="text">List filter</span></th>
            </tr>
        </thead>
        <tbody>
            <tr class="even-line inactive-class">
                <td>
                    Select category to show entries.
                    <div class="caetgory">
                        <strong>CATEGORY</strong>
                        <select name="category"><?php
                            echo '<option value="0">all</option>';
                            reset($categories);
                            while(list($key, $value) = each($categories)) {
                                $id = $value['id'];
                                $name = htmlspecialchars($value['name']);
                                $selected = $category == $id ? ' selected' : '';
                                echo '<option value="' . $id . '"' . $selected . '>' . $id . ' :: ' . $name . '</option>';
                            }

                            $disables = $entry && $entry > 0 ? '' : ' disabled';
                            ?></select>
                    </div>
                    <div class="article">
                        <strong>ENTRY</strong>
                        <select name="entry"></select>
                    </div>
                    <div>
                        <input type="submit" value="search">
                    </div>
                </td>
            </tr>
        </tbody>
    </table>
</form>
<form method="get" action="<?php echo $suri['url'] ?>">
    <div>
        <input type="hidden" name="name" value="<?php echo $_GET['name'] ?>">
        Select entry <select name="entry"><?php
            echo '<option value="0">all</option>';
            reset($entries);
            while(list($key, list($num, $title)) = each($entries)) {
                $title = htmlspecialchars($title);
                $selected = $entry == $num ? ' selected' : '';
                echo '<option value="' . $num . '"' . $selected . '>' . $num . ' :: ' . $title . '</option>';
            }

            $disables = $entry && $entry > 0 ? '' : ' disabled';
        ?></select>
        <input type="submit" value="go">
        &nbsp;batch – &nbsp;
        <input type="button" name="batchToggleOn" value="on" data-entry="<?php echo $entry ?>"<?php echo $disables ?>>
        <input type="button" name="batchToggleOff" value="off" data-entry="<?php echo $entry ?>"<?php echo $disables ?>>
        <input type="button" name="batchDelete" value="delete" data-entry="<?php echo $entry ?>"<?php echo $disables ?>>
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
                $preview_image = $defaultURL . substr($item['url'], stripos($item['url'], 'attach') - 1);
            }

            $data_tooltip = json_pretty_encode($data);

            $uniq = json_encode(array(
                'type' => $item['type'],
                'entry_id' => $item['entry_id'],
                'url' => $item['url']
            ));
    ?>
        <tr>
            <td><?php echo $item['entry_id'] ?></td>
            <td><?php echo $item['type'] == 0 ? 'blog' : 'other' ?></td>
            <td><a href="<?php echo $preview_image ?>" target="_blank" class="preview"><img src="<?php echo $preview_image ?>" alt="preview image" width="auto" height="20px"></a></td>
            <td><a class="tooltip" title="<?php echo $data_tooltip ?>"><?php echo $preview ?></a></td>
            <td><a href="#" class="toggleEnabled toggler<?php echo $item['is_enabled'] == 1 ? '' : ' off' ?>" data-data='<?php echo $uniq ?>'>&nbsp;</a></td>
            <td><input type="button" name="deleteExif" value="DELETE" data-data='<?php echo $uniq ?>'></td>
        </tr>
    <?php
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

<script>var baseURL = "<?php echo $defaultURL ?>";</script>
<script src="<?php echo $pluginURL ?>/images/admin.js"></script>
<?php
}
?>