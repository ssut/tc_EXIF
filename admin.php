<?php
function EXIF_admin_load() {
    $entry = isset($_GET['entry']) ? intval($_GET['entry']) : 0;

    $ctx = Model_Context::getInstance();

    $db = DBModel::getInstance();
    $db->reset('ExifCaches');
    $db->setQualifier('entry_id', 'eq', $entry, true);
    $count = $db->getCount();

    if($count == 0 && $entry == 0) {
        $db->reset('ExifCaches');
    }

    $entries = array_unique($db->getAll('entry_id'));
    $entries = array_map(function($value) {
        return $value['entry_id'];
    }, $entries);

?>
<form method="get">
    <div>
        <input type="hidden" name="name" value="<?php echo $_GET['name'] ?>">
        Select entry number: <select name="entry"><?php
            for($i = 0, $cnt = count($entries); $i < $cnt; $i++) {
                $num = $entries[$i];
                $selected = $entry == $num ? ' selected' : '';
                echo '<option></option>';
                echo '<option value="' . $num . '"' . $selected . '>' . $num . '</option>';
            }
        ?></select>
    </div>
</form>
<?
}
?>