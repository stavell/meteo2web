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
