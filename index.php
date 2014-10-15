<?php
function EXIF_default_css($target) {
    $target .= <<<HTML
<style type="text/css">
div.tc_EXIF {
}
div.tc_EXIF dl {
    font-size: 0.6em;
}
div.tc_EXIF dl dt {
    display: /*inline-block*/none;
    margin: 0;
    padding: 0 5px;
}
div.tc_EXIF dl dd {
    display: inline-block;
    margin: 0;
    padding: 0 5px;
    word-break: break-all;
}
div.tc_EXIF:after {
    content: '';
    display: table;
    clear: both;
}
</style>
HTML;
    return $target;
}

function EXIF_attached_image($target, $mother) {
    global $configVal, $entry;
    requireComponent('Textcube.Function.Setting');
    $config = misc::fetchConfigVal($configVal);
    if(!isset($entry['id'])) return $target;
    if(is_null($config)) return $target;
    if(array_key_exists('attachedImage', $config) === false ||
        $config['attachedImage'] !== '1') return $target;
    $ext = misc::getFileExtension($mother);
    if($ext !== 'jpg' && $ext !== 'jpeg') return $target;
    unset($ext);

    $attachment = ROOT . '/attach/' . getBlogId() . '/' . basename($mother);
    $exif = EXIF_cache(0, $entry['id'], $attachment);
    if($exif === true) return $target;
    if($exif === false) {
        $exif = extract_EXIF($attachment);
        if($exif === false) {
            EXIF_cache(0, $entry['id'], $attachment, array('NoEXIF' => 1));
            return $target;
        }

        EXIF_cache(0, $entry['id'], $attachment, $exif);
    }

    $target = '<div class="tc_EXIF">' . $target;
    $target .= EXIF_tagging($exif);
    $target .= '</div>';
    return $target;
}

function EXIF_other_image($target) {
    if(ini_get('allow_url_fopen') !== '1') return $target;

    global $configVal, $entry, $defaultURL;
    requireComponent('Textcube.Function.Setting');
    $config = misc::fetchConfigVal($configVal);
    if(!isset($entry['id'])) return $target;
    if(is_null($config)) return $target;
    if(array_key_exists('otherImage', $config) === false ||
        $config['otherImage'] !== '1') return $target;

    $images = array(); // [tag, src] ...
    $pattern = '/(<img[^>]+>)/i';
    $src_pattern = '/src="(.*?)"/i';
    if(preg_match_all($pattern, $target, $matches)) {
        if(isset($matches[0]) && count($matches[0]) > 0) {
            foreach($matches[0] as $image) {
                if(stripos($image, $defaultURL) !== false &&
                    stripos($image, 'attach') !== false) continue;
                if(preg_match($src_pattern, $image, $src_matches)) {
                    if(!isset($src_matches[1])) continue;
                    $src = $src_matches[1];
                    if(stripos($src, '.jpg') !== false ||
                        stripos($src, '.jpeg') !== false) {
                        $images[] = array($image, $src);
                    }
                }
            }
        }
    }
    unset($pattern);
    unset($src_pattern);

    foreach($images as list($tag, $url)) {
        $exif = EXIF_cache(1, $entry['id'], $url);
        if($exif === true) continue; // no exif
        if($exif === false) {
            $exif = extract_EXIF($url);
            if($exif === false) {
                EXIF_cache(1, $entry['id'], $url, array('NoEXIF' => 1));
                continue;
            }

            EXIF_cache(1, $entry['id'], $url, $exif);
        }

        $append = EXIF_tagging($exif);
        $new = '<div class="tc_EXIF tc_EXIF_other">' . $tag . $append . '</div>';
        $target = str_replace($tag, $new, $target);
    }

    return $target;
}

function EXIF_tagging($exif) {
    global $configVal, $entry;
    requireComponent('Textcube.Function.Setting');
    $config = misc::fetchConfigVal($configVal);
    $code = '';

    // not Google Maps :p
    $maps = array(
        'ex1' => 'Make',
        'ex2' => 'Model',
        'ex3' => 'ExposureMode',
        'ex4' => 'MeteringMode',
        'ex5' => 'WhiteBalance',
        'ex6' => 'ExposureTime',
        'ex7' => 'FNumber',
        'ex8' => 'MaxAperture',
        'ex9' => 'ExposureBias',
        'ex10' => 'FocalLength',
        'ex11' => 'FocalLengthFilm',
        'ex12' => 'ISO',
        'ex13' => 'Flash',
        'ex14' => 'DateTime',
        'ex15' => 'Software',
    );

    $matches = array();

    foreach($maps as $ex => $key) {
        if(array_key_exists($ex, $config) && $config[$ex] &&
            !is_null($exif[$key]) && !empty($exif[$key])) {
            $matches[$key] = $exif[$key];
        }
    }

    if(count($matches) === 0) return '';
    $code = '<dl>';
    foreach($matches as $key => $value) {
        $code .= '<dt data-key="' . $key . '">' . $key . '</dt>';
        $code .= '<dd>' . htmlspecialchars($value) . '</dd>';
    }
    $code .= '</dl>';

    return $code;
}

function EXIF_cache($type, $entry_id, $url, $set = null) {
    $db = DBModel::getInstance();
    $db->reset('ExifCaches');
    $type = POD::escapeString($type);
    $entry_id = POD::escapeString($entry_id);

    if(!is_null($set)) {
        $data = json_encode($set);

        $db->setAttribute('type', $type);
        $db->setAttribute('entry_id', $entry_id);
        $db->setAttribute('url', $url, true);
        $db->setAttribute('data', $data, true);
        $result = $db->replace();

        return $result;
    }

    $db->setQualifier('type', 'eq', $type);
    $db->setQualifier('entry_id', 'eq', $entry_id);
    $db->setQualifier('url', 'eq', $url, true);
    $row = $db->getRow();
    if(is_null($row) || empty($row)) return false;
    $data = json_decode($row['data'], true);
    if(array_key_exists('NoEXIF', $data) || $row['is_enabled'] === 0) return true;

    return $data;
}

function EXIF_dataset($data) {
    $cfg = misc::fetchConfigVal($data);
    return true;
}

function extract_EXIF($path) {
    require_once(dirname(__FILE__) . '/lib/PelDataWindow.php');
    require_once(dirname(__FILE__) . '/lib/PelJpeg.php');

    $data = null;
    try {
        $content = file_get_contents($path);
        $data = new PelDataWindow($content);
    } catch (Exception $e) {
        return false;
    } finally {
        unset($content);
    }
    if(!PelJpeg::isValid($data)) return false;

    $jpeg = new PelJpeg();
    $jpeg->load($data);
    $exif = $jpeg->getExif();
    if(is_null($exif)) return false;

    $tiff = $exif->getTiff();
    if(is_null($tiff)) return false;

    $ifd0 = $tiff->getIfd();

    $info = array();
    $entries = array();

    $entries += $ifd0->getEntries();
    foreach($ifd0->getSubIfds() as $id => $val) {
        $entries += $val->getEntries();
    }

    set_or_null($info, 'Make', PelTag::MAKE, $entries);
    set_or_null($info, 'Model', PelTag::MODEL, $entries);
    set_or_null($info, 'ExposureMode', PelTag::EXPOSURE_PROGRAM, $entries);
    set_or_null($info, 'MeteringMode', PelTag::METERING_MODE, $entries);
    set_or_null($info, 'WhiteBalance', PelTag::WHITE_BALANCE, $entries);
    set_or_null($info, 'ExposureTime', PelTag::EXPOSURE_TIME, $entries);
    set_or_null($info, 'FNumber', PelTag::FNUMBER, $entries);
    set_or_null($info, 'MaxAperture', PelTag::MAX_APERTURE_VALUE, $entries);
    set_or_null($info, 'ExposureBias', PelTag::EXPOSURE_BIAS_VALUE, $entries);
    set_or_null($info, 'FocalLength', PelTag::FOCAL_LENGTH, $entries);
    set_or_null($info, 'FocalLengthFilm', PelTag::FOCAL_LENGTH_IN_35MM_FILM, $entries);
    set_or_null($info, 'ISO', PelTag::ISO_SPEED_RATINGS, $entries);
    set_or_null($info, 'Flash', PelTag::FLASH, $entries);
    set_or_null($info, 'DateTime', PelTag::DATE_TIME, $entries);
    set_or_null($info, 'Software', PelTag::SOFTWARE, $entries);

    if(is_null($info['FocalLengthFilm']) && !is_null($info['FocalLength']) &&
        !empty($info['FocalLength'])) {
        $info['FocalLengthFilm'] = $info['FocalLength'];
    }

    if(!is_null($info['FocalLengthFilm']) && is_int($info['FocalLengthFilm'])) {
        $info['FocalLengthFilm'] = number_format(
            intval($info['FocalLengthFilm']), 1, '.', '') . ' mm';
    }

    if(!is_null($info['MaxAperture']) && stripos($info['MaxAperture'], '/') !== false) {
        list($numerator, $denominator) = array_map(
            'intval', explode('/', $info['MaxAperture']));
        $max_aperture = '';
        try {
            $max_aperture = 'f/' . number_format(
                $numerator / $denominator, 1, '.', '');
        } catch (Exception $e) {
        }

        if($max_aperture !== '') {
            $info['MaxAperture'] = $max_aperture;
        }
    }

    return $info;
}

function set_or_null(&$array, $key, $needle, &$haystack) {
    $array[$key] = null;
    if(array_key_exists($needle, $haystack)) {
        $array[$key] = $haystack[$needle]->getText();
    }
}

?>