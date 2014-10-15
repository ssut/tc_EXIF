# EXIF Plugin for Textcube

![](http://puu.sh/ccVGu/bd732bf58e.png)

tc_EXIF displays EXIF meta data for images attached with textcube.

the purpose of this plugin is to make displaying EXIF data as convenient as possible.

this plugin requires **PHP 5.3.3 upwards**. because this uses anonymous functions which are not supported in PHP < 5.3.3.

## Features

* an EXIF library written in pure PHP: unlike other EXIF libraries for PHP, tc_EXIF(PEL) does not require C extension and `exec` function.
* EXIF cache using textcube storage -- no performance decline after cached
* supports widely used EXIF tags such as Make, Model, Exposure, Aperture, Focal Length
* fully work in latest version of textcube (tested on 1.10 RC1)
* supports external images (e.g. http://example.com/a.jpg)
* management panel

## What is EXIF?

EXIF is the abbreviation of 'Exchangeable Image File format'.

EXIF data is a type of metadata that is imbedded in photographers that records a number of details.

the EXIF data can contain any or all of the following:

* Date and time the picture was taken
* Camera settings (check out the 'EXIF Tags' section below)
* Camera lens information (Name, Model, Serial..)
* Software that was used to edit
* Coordinates (longitude-latitude)

## EXIF Tags

* Make
* Model
* ExposureMode (Exposure program)
* MeteringMode
* WhiteBalance
* ExposureTime (Shutter Speed)
* FNumber (Aperture Number like f/4.0)
* MaxAperture (Max Aperture Number like f/1.4)
* ExposureBias (EV)
* FocalLength
* FocalLengthFilm (equiv 35mm)
* ISO (ISO Speed)
* Flash
* DateTime
* Software

Coordinates and Lens is not supported yet.

## Installation

1. Upload the tc_EXIF folder to the */plugins* directory.
2. Activate the EXIF plugin through the 'PlugIn' menu in textcube admin page.

## Configuration

### Plugin Settings

![](http://puu.sh/ccVMY/b2fff30486.png)

* Attached images: images attached with a textcube uploader. (will be stored in a textcube `attach` directory)

* Other images: external images

* If you've checked "insert CSS codes for pretty print" setting, this plugin will add the default CSS code just before the closing `</head>` tag.

Here is the default CSS code:
```css
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
```

### Management Panel

![](http://puu.sh/ccW6f/06ee7c78b5.png)

You can manage cached EXIF data in textcube admin page.

* article selection (descending order by article number)
* pagination (15 EXIFs per page)
* enabled: you can toggle the visibility show(on) and hide(off) using that button.
* delete: delete EXIF data
  * the EXIF data will be generated when anyone open your article. -- **WARNING:** this procedure represents a substantial cost to the web server.
* hover your mouse over the picture to zoom:

![](http://puu.sh/ccYaN/49617d2f1c.png)

* hover your mouse over the data to show details:

![](http://puu.sh/ccYcb/c7cf607a28.png)


## Customization

Here is an example of HTML output generated by textcube with this plugin:

```html
<div class="imageblock center" style="text-align: center; clear: both;">
  <div class="tc_EXIF">
    <img src="http://b.ssut.me/attach/1/7734476405.jpg" alt="" height="906" width="680" />
    <dl>
      <dt data-key="Make">
        Make
      </dt>
      <dd>
        Apple
      </dd>
      <dt data-key="Model">
        Model
      </dt>
      <dd>
        iPhone 5s
      </dd>
      <dt data-key="ExposureMode">
        ExposureMode
      </dt>
      <dd>
        Normal program
      </dd>
      <dt data-key="MeteringMode">
        MeteringMode
      </dt>
      <dd>
        Spot
      </dd>
      <dt data-key="WhiteBalance">
        WhiteBalance
      </dt>
      <dd>
        Auto white balance
      </dd>
      <dt data-key="ExposureTime">
        ExposureTime
      </dt>
      <dd>
        1/60 sec.
      </dd>
      <dt data-key="FNumber">
        FNumber
      </dt>
      <dd>
        f/2.2
      </dd>
      <dt data-key="FocalLength">
        FocalLength
      </dt>
      <dd>
        4.1 mm
      </dd>
      <dt data-key="FocalLengthFilm">
        FocalLengthFilm
      </dt>
      <dd>
        30.0 mm
      </dd>
      <dt data-key="ISO">
        ISO
      </dt>
      <dd>
        50
      </dd>
      <dt data-key="Flash">
        Flash
      </dt>
      <dd>
        Flash did not fire, compulsory flash mode.
      </dd>
      <dt data-key="DateTime">
        DateTime
      </dt>
      <dd>
        2014:06:21 18:26:44
      </dd>
      <dt data-key="Software">
        Software
      </dt>
      <dd>
        Microsoft Windows Photo Viewer 6.2.9200.16384
      </dd>
    </dl>
  </div>
</div>
```

and the following CSS code is associated with the above HTML code:

```css
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
```

![](http://puu.sh/ccWQb/f7dfaa45b5.png)

I think this is the best way to customize the information. If you have better idea on how to do it, please create an issue on this repository. (I will happy to go that route :p)

## Structure

* Textcube Plugin
  * Listener
     * ViewAttachedImage &rarr; EXIF\_attached\_image : To make EXIF data from attached images
     * ViewPostContent &rarr; EXIF\_other\_image : To make EXIF data from other images
     * DeletePost &rarr; EXIF\_delete\_post : To delete EXIF data of the article
     * /plugins/EXIF/toggle &rarr; EXIF\_toggle
     * /plugins/EXIF/delete &rarr; EXIF\_delete
  * Tag
     * SKIN\_head\_end &rarr; EXIF\_default\_css
  * Storage
     * Table: ExifCaches
         * type
         * entry_id
         * url
         * data
         * is_enabled

## Support 

The developer reside in [Ozinger IRC](http://ozinger.org/) and [Freenode IRC](http://freenode.net/) and would be glad to help you. (query me **ssut**)

If you think you have a found a bug/have a idea/suggestion, please **open a issue** or **send a pull request** here on GitHub.

## License

Licensed under the indrecibly [permissive](https://en.wikipedia.org/wiki/Permissive_free_software_licence) [MIT license](http://opensource.org/licenses/mit-license.php).

The Terms are as follows.

```
The MIT License (MIT)

Copyright (c) 2014 SuHun Han

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
```

