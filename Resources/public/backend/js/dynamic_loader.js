DynamicLoader = function() {
    this.scriptList = {};
    this.styleList = {};
    this.callback = null;
};

DynamicLoader.prototype.addScript = function(id, src) {
    this.scriptList[id] = {
        src: src,
        loaded: false
    };
};

DynamicLoader.prototype.addStyle = function(id, href) {
    this.styleList[id] = {
        href: href,
        loaded: false
    };
};

DynamicLoader.prototype.load = function(callback) {
    this.callback = callback;
    var body = document.getElementsByTagName('body')[0];
    for(var key in this.scriptList) {
        var script = document.createElement('script');
        script.type = 'text/javascript';
        script.src = this.scriptList[key].src;
        script.id = key;

        var loader = this;
        script.onload = function(){loader.markScriptLoaded(this.id)};
        //The great IE doesn't support onload event, a special one is required just for him
        script.onreadystatechange = function(){
            if('loaded' == this.readyState){
                loader.markScriptLoaded(this.id);
            }
        };

        body.appendChild(script);
    }

    var head = document.getElementsByTagName('head')[0];
    for(key in this.styleList) {
        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = this.styleList[key].href;

        head.appendChild(link);
    }
};

DynamicLoader.prototype.markScriptLoaded = function(id){
    this.scriptList[id].loaded = true;
    for (var key in this.scriptList) {
        if(!this.scriptList[key].loaded) {
            return;
        }
    }

    if (this.callback) {
        this.callback();
    }
};