<?php
function EXIF_attached_image($target, $mother) {
    global $configVal;
    requireComponent('Textcube.Function.Setting');
    $config = misc::fetchConfigVal($configVal);
    if(is_null($config)) return $target;
    if(array_key_exists('attachedImage', $config) === false ||
        $config['attachedImage'] !== '1') return $target;
    $ext = misc::getFileExtension($mother);
    if($ext !== 'jpg' && $ext !== 'jpeg') return $target;
    unset($ext);

    $attachPath = ROOT . '/attach/' . getBlogId() . '/' . basename($mother);
    var_dump(extract_EXIF($attachPath));

    return $target;
}

function EXIF_other_image($target) {
    global $owner, $suri, $configVal;
    requireComponent('Textcube.Function.Setting');
    $config = misc::fetchConfigVal($configVal);
    if(is_null($config)) return $target;
    if(array_key_exists('otherImage', $config) === false ||
        $config['otherImage'] !== '1') return $target;

    return $target;
}

function EXIF_dataset($data) {
    $cfg = misc::fetchConfigVal($data);
    return true;
}

function extract_EXIF($path) {
    require_once(dirname(__FILE__) . '/lib/PelDataWindow.php');
    require_once(dirname(__FILE__) . '/lib/PelJpeg.php');

    $data = new PelDataWindow(file_get_contents($path));
    if(!PelJpeg::isValid($data)) return false;

    $jpeg = new PelJpeg();
    $jpeg->load($data);
    $exif = $jpeg->getExif();
    $tiff = $exif->getTiff();
    $ifd0 = $tiff->getIfd();

    if(is_null($exif)) return false;

    $info = array();
    $entries = array();

    $entries += $ifd0->getEntries();
    foreach($ifd0->getSubIfds() as $id => $val) {
        $entries += $val->getEntries();
    }

    set_or_null($info, 'Make', PelTag::MAKE, $entries);
    set_or_null($info, 'Model', PelTag::MODEL, $entries);
    set_or_null($info, 'ExposureProgram', PelTag::EXPOSURE_PROGRAM, $entries);
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