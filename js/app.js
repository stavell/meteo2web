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
        App.ImgLoader.readyFiles.push(this.src);
        if(App.ImgLoader.onProgressUpdate) App.ImgLoader.onProgressUpdate(App.ImgLoader.readyFiles.length);
        if(App.ImgLoader.onFinish && App.ImgLoader.readyFiles.length == App.ImgLoader.files.length) App.ImgLoader.onFinish();
    },
    onFinish: null,
    onProgressUpdate: null
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

        el.onImageChanged = params.onImageChanged || function(){};

        el.setFiles = function(files) {
            el.files = files;
            el.fileIndex = 0;
            el.setImageFromIndex(el.fileIndex);
        };

        el.showNext = function() {
            if(!el.getFileByIndex(el.fileIndex+1)) return;
            el.fileIndex++;
            return el.setImageFromIndex(el.fileIndex);
        };

        el.showPrev = function() {
            if(!el.getFileByIndex(el.fileIndex-1)) return;
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

            el.onImageChanged.apply(el,[file]);
            return file;
        }

        el.getFileByIndex = function(idx) {
            if(el.files[idx]) return el.files[idx];
            return false;
        }
    });

};