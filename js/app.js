BASE_URL = 'http://stavl.com/meteo2/';

var App = {};

App.timeParams = {
    timeFrom: '- 1hour',
    period:60,
    asc: false
};

App.ImgLoader = {
    files: [],
    readyFiles:[],
    cache:{},
    setFiles: function(files) {
        App.ImgLoader.init();
        App.ImgLoader.files = files;
    },
    init: function(){
        App.ImgLoader.readyFiles = [];
        App.ImgLoader.cache = {};
    },
    startLoading: function() {
        for(var idx in App.ImgLoader.files) {
            var file = App.ImgLoader.files[idx];
            var src = file.url;
            App.ImgLoader.cache[src] = new Image();
            App.ImgLoader.cache[src].onload = App.ImgLoader.onImageLoaded;
            App.ImgLoader.cache[src].src = src;
        }
    },
    onImageLoaded: function() {
        if(App.ImgLoader.imageFilter) if(!App.ImgLoader.imageFilter.apply(this,[])) {
            for(var idx in App.ImgLoader.files) {
                if(App.ImgLoader.files[idx].url != this.src) continue;
                return App.ImgLoader.files.splice(idx,1);
            }
        }

        App.ImgLoader.readyFiles.push(this.src);
        if(App.ImgLoader.onProgressUpdate) App.ImgLoader.onProgressUpdate.apply(this,[App.ImgLoader.readyFiles.length,App.ImgLoader.files.length]);
        if(App.ImgLoader.onFinish && App.ImgLoader.readyFiles.length == App.ImgLoader.files.length) App.ImgLoader.onFinish();
    },
    onFinish: null,
    onProgressUpdate: null,
    imageFilter: null
};

App.getFormatedTime = function(date) {
    return (date.getHours()< 9 ? '0':'')+date.getHours() + ':' + (date.getMinutes()< 10 ? '0':'') + date.getMinutes();
};




App.isMobileBrowser =  /Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent);


App.initCameraViewer = function(obj, params) {

    $(obj).each(function(){
        var el = $(this)[0];
        if(!el) return;

        el.fileIndex = 0;
        el.delay = 500; //ms

        el.interval = -1;
        el.timeout = -1;

        el.onImageChanged = params.onImageChanged || function(){};



        el.setFiles = function(files) {
            el.files = files;
            el.resetFileIndex();
            el.setImageFromIndex(el.fileIndex);
        };


        el.showNext = function(bLoop) {
            el.pauseSlideshow();
            return el._showNext(bLoop);
        };


        el.showPrev = function(bLoop){
            el.pauseSlideshow();
            return el._showPrev(bLoop);
        };

        el._showNext = function(bLoop) {
            if(!el.getFileByIndex(el.fileIndex+1)) {
                if(bLoop) {
                    el.resetFileIndex(false);
                    return el._showNext(bLoop);
                }
                return;
            }
            el.fileIndex++;

            return el.setImageFromIndex(el.fileIndex);
        };


        el._showPrev = function(bLoop) {
            if(!el.getFileByIndex(el.fileIndex-1)) {
                if(bLoop) {
                    el.resetFileIndex(true);
                    return el._showPrev(bLoop);
                }
                return;
            }
            el.fileIndex--;

            return el.setImageFromIndex(el.fileIndex);
        };

        el.setImageFromIndex = function(idx) {
            var file = el.getFileByIndex(idx);
            if(!file) return false;

            if(el.nodeName.toLowerCase() == 'img') {
                $(el).attr({src:file.url});
            } else {
                $(el).css('background-image','url('+file.url+')');
            }

            el.onImageChanged.apply(el,[file, el.fileIndex]);
            return file;
        };

        el.getFileByIndex = function(idx) {
            if(el.files[idx]) return el.files[idx];
            return false;
        };

        el.resetFileIndex = function(bEnd) {
            el.fileIndex = bEnd ? el.files.length-1 : 0;
        };

        el.startSlideshow = function(time) {
            el.stopSlideshow();
            el.delay = time || el.delay;
            el.interval = setInterval(function(){el._showNext(true);}, el.delay);
        };

        el.stopSlideshow = function() {
            if(el.interval < 0) return;
            clearInterval(el.interval);
            el.interval = -1;
        };

        el.pauseSlideshow = function(pauseTime) {
            el.stopSlideshow();
            if(el.timeout > -1) clearTimeout(el.timeout);

            el.timeout = setTimeout(function(){el.startSlideshow()}, pauseTime || 3500);
        }
    });
};

App.getUrlVar = function(key){
    var result = new RegExp(key + "=([^&]*)", "i").exec(window.location.search);
    return result && unescape(result[1]) || "";
}
