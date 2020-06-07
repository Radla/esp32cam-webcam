<!-- https://stackoverflow.com/questions/15415970/how-to-pass-php-directory-files-into-javascript-array 
http://fuzzytolerance.info/blog/2017/07/20/HTML-range-input-with-snapping/
https://stackoverflow.com/questions/49135398/how-to-convert-millisecond1304921325178-3193-to-yyyy-mm-dd-in-javascript
-->

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Timeslider</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <style>
    body {
        font-family: 'Arial', 'sans serif';
        margin: 0;
        padding: 0;
    }

    .container {
        max-width: 1400px;
        margin: 0 auto;
    }

    .row {
        box-sizing: border-box;
        display: flex;
        flex: 0 1 auto;
        flex-direction: row;
        flex-wrap: wrap;
        /*margin-right: -.5rem;
        margin-left: -.5rem;*/
    }

    .col-xs {
        box-sizing: border-box;
        flex: 0 0 auto;
        padding-right: .5rem;
        padding-left: .5rem;
        flex-grow: 1;
        flex-basis: 0;
        max-width: 100%;
    }

    .slidecontainer {
        width: 100%;
    }

    .slider {
        -webkit-appearance: none;
        width: 100%;
        height: 25px;
        background: #d3d3d3;
        outline: none;
        opacity: 0.7;
        -webkit-transition: .2s;
        transition: opacity .2s;
    }

    .slider:hover {
        opacity: 1;
    }

    .slider::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 25px;
        height: 25px;
        background: #4CAF50;
        cursor: pointer;
    }

    .slider::-moz-range-thumb {
        width: 25px;
        height: 25px;
        background: #4CAF50;
        cursor: pointer;
    }

    .form {
        display: flex;
        justify-content: space-between;
        align-items: baseline;
        margin: .3rem 0;
    }

    img {
        display: block;
        max-width: 100%;
    }

    nav {
        margin: 0.5rem 0;
    }

    figure, input {
        margin-left: 0;
        margin-right: 0;
    }
    #scale {
        border-bottom: 1px solid grey;
        
        height: 10px;
        width: 100%;
        position: relative;
        box-sizing: border-box;
    }
    .vline {
        position: absolute;
        bottom: 0;
        height: 5px;
        width: 1px;
        background: black;
    }
    </style>
</head>

<body>
    <div class="row">
        <div class="col-xs">
            <div class="container">
            <figure id="image-container">
                    <img alt="webcam image" id="img" />
                </figure>
                <nav>
                    <div class="slidecontainer">
                    <div id="scale"></div>
                        <input type="range" value="0" class="slider" id="myRange">
                        <div class="form">
                            <div class="start">
                                <label for="startDate">Start date:</label>
                                <input type="date" id="startDate" name="Start Datum" value="2020-03-31" min="2020-03-31"
                                    max="2020-04-10">
                            </div>
                            <div class="curr">
                                <span id="currDate"></span>
                            </div>
                            <div class="end">
                                <label for="endDate">End date:</label>
                                <input type="date" id="endDate" name="End Datum" value="2020-04-04" min="2020-03-31"
                                    max="2020-04-10">
                            </div>
                        </div>
                    </div>
                </nav>
            </div>
        </div>
    </div>
    <script>
    <?php
        $_filelist = [];
        foreach (new DirectoryIterator('.') as $item) {
            if (strtolower(substr($item, -4)) == ".jpg" && $item->isFile() && (empty($file))) {
                $_filelist[] = $item->getPathname();
            }
        }
        //$_sorted = sort($_filelist);
        sort($_filelist, SORT_NATURAL | SORT_FLAG_CASE);
        echo "var imageList = " . json_encode($_filelist) . ";";
    ?>
    var slider = document.querySelector("#myRange");
    var output = document.querySelector("#currDate");
    var startDate = document.querySelector("#startDate");
    var endDate = document.querySelector("#endDate");
    var img = document.querySelector("#img");
    var img_container = document.querySelector("#image-container");
    var scale= document.querySelector("#scale");
    var secondsADay = 86400000;

    var length = imageList.length;
    // Daten umwandeln in ms, zu einfacheren Berechnung
    var listMilli = [];
    for (var i = 0; i < length; i++) {
        var m = filename2millis(imageList[i]);
        listMilli.push(m);
    }
    // cam_20-04-04-103308.jpg
    var endDateIdx = length - 1;
    var startDateIdx = endDateIdx;
    // erstmal nur die letzten 100 Bilder
    if (length > 100) {
        startDateIdx = endDateIdx - 100;
    }
    // aktuellste zuerst
    var currDateIdx = endDateIdx;
    
    slider.min = listMilli[startDateIdx];
    slider.max = listMilli[endDateIdx];

    startDate.min = date2date(listMilli[0]);
    startDate.max = date2date(listMilli[length-1]);
    endDate.min = date2date(listMilli[0]);
    endDate.max = date2date(listMilli[length-1]);

    // muss verzögert erfolgen
    setTimeout(function() {
        startDate.value = date2date(listMilli[startDateIdx]);
        endDate.value = date2date(listMilli[endDateIdx]);
        slider.value = listMilli[currDateIdx];
        output.innerHTML = milli2date(listMilli[currDateIdx]);
        img.src = imageList[currDateIdx];
    }, 100);

    function prevImage() {
        currDateIdx--;
        if (currDateIdx < startDateIdx) currDateIdx = startDateIdx;
        setSlider(listMilli[currDateIdx]);
    }
    function nextImage() {
        currDateIdx++;
        if (currDateIdx > endDateIdx) currDateIdx = endDateIdx;
        setSlider(listMilli[currDateIdx]);
    }
    function prevDayImage() {
        var currVal = listMilli[currDateIdx];
        currVal -= secondsADay;
        setSlider(currVal);
    }
    function nextDayImage() {
        var currVal = listMilli[currDateIdx];
        currVal += secondsADay;
        setSlider(currVal);
    }

    document.addEventListener('keydown', (event) => {
        const keyName = event.key;

        if (keyName === 'ArrowLeft') {
            prevImage();
            return;
        }
        if (keyName === 'ArrowRight') {
            nextImage();
            return;
        }
        if (keyName === 'ArrowUp') {
            nextDayImage();
            return;
        }
        if (keyName === 'ArrowDown') {
            prevDayImage();
            return;
        }
    });
    slider.focus();

    var prevIndex = 0;

    // noch nicht optimal
    function setSlider(val) {
        var closest = getClosest(listMilli, val);
        slider.value = closest;
        currDateIdx = idxFromMillis(closest);
        if (currDateIdx != prevIndex) {
            prevIndex = currDateIdx;
            var d = milli2date(val);
            output.innerHTML = d;
            img.src = imageList[currDateIdx];
        }

    }

    slider.oninput = function() {
        setSlider(this.value);
    };

    img_container.addEventListener('click', function (e) {
        var fakX = e.offsetX / e.target.clientWidth;
        var fakY = e.offsetY / e.target.clientHeight;

        if (fakY < .1 || fakY > .9) {
            // daymode
            if (fakY < .1) {
                nextDayImage();
            } else {
                prevDayImage();
            }
        } else if (fakX > 0.4) {
            nextImage()
        } else {
            prevImage();
        }
        e.preventDefault();
    });

    function addScaleEl(posX) {
        var newElement = document.createElement('span'); 
        newElement.setAttribute('class', 'vline'); 
        newElement.style.left = posX+'%';
        scale.appendChild(newElement);
    }
    

    function getCurrSliderValues() {
        scale.innerHTML = "";
        var min = parseInt(slider.getAttribute('min'));
        var max = parseInt(slider.getAttribute('max'));
        // 25px vom slider abziehen
        //trace("w: "+slider.clientWidth-25);
        var p = 25/(parseInt(slider.clientWidth));

        for (var i=0;i<length;i++) {
            var el = listMilli[i];
            if (el > min && el < max) {
                var normalized = (el-min)/(max-min);
                addScaleEl((normalized+p/2)*(100-(p)*100));
                
            }
        }
        
    }
    getCurrSliderValues();
/*
    var valueHover = 0;
    function calcSliderPos(e) {
        var min = parseInt(e.target.getAttribute('min'));
        return  ((e.offsetX / e.target.clientWidth) *  parseInt(e.target.getAttribute('max')) + min);
    }

    slider.addEventListener('mousemove', function(e) {
        valueHover = calcSliderPos(e);
        trace(valueHover);
        
        var closest = getClosest(listMilli, valueHover);
        var d = milli2date(closest);
        output.innerHTML = d;
    });

    slider.addEventListener('mouseout', function(e) {
        var d = milli2date(slider.value);
        output.innerHTML = d;
        trace("off: "+e.offsetX +" : w: "+ e.target.clientWidth+" : fak: "+(e.offsetX / e.target.clientWidth));
    });
    */
    startDate.addEventListener('change', function() {
        if (this.value > endDate.value) return;
        var date = this.value;
        var d = new Date(date);
        var m = d.getTime();
        startDate.value = date2date(m);
        var closest = getClosest(listMilli, m);
        startDateIdx = idxFromMillis(closest);
        slider.min = listMilli[startDateIdx];
        getCurrSliderValues();
    });

    endDate.addEventListener('change', function() {
        if (this.value < startDate.value) return;
        var date = this.value;
        var d = new Date(date);
        d.setHours(23, 55, 00);
        var m = d.getTime();
        endDate.value = date2date(m);
        var closest = getClosest(listMilli, m);
        endDateIdx = idxFromMillis(closest);
        slider.max = listMilli[endDateIdx];
        getCurrSliderValues();
    });

    function date2date(millis) {
        var date = new Date(millis);
        var year = date.getFullYear();
        var month = ("0" + (date.getMonth() + 1)).slice(-2);
        var day = ("0" + date.getDate()).slice(-2);
        return year + "-" + month + "-" + day;
    }

    function milli2date(val) {
        return new Date(parseInt(val)).toLocaleString('de-DE');
    }

    function idxFromMillis(millis) {
        return listMilli.indexOf(millis);
    }

    // snap in
    function getClosest(arr, val) {
        return arr.reduce(function(prev, curr) {
            return (Math.abs(curr - val) < Math.abs(prev - val) ? curr : prev);
        });
    }

    function filename2millis(filename) {
        // anfang und ende raus
        var s = filename.replace(/^\.\/cam_(.*)\.jpg/g, '$1');
        // doppelpunkte in zeit
        s = s.replace(/\-(\d\d)(\d\d)/g, ' $1\:$2\:')
        // - im datum durch / ersetzen + 20 hinzufügen
        s = s.replace(/(\d\d)[-](\d\d)[-](\d\d)/g, '20$1\/$2\/$3');
        var date = new Date(s);
        return date.getTime();
    }

    function trace(s) {
        try {
            console.log(s);
        } catch (e) {
            /*alert(s); */
        }
    }

    function showProp(obj) {
        for (var key in obj) trace(key + " : " + obj[key]);
    }
    </script>
</body>

</html>