/**
 * 异步延时加载
 * Created by Lin on 2015/5/16.
 * Email : 784855684@qq.com
 * Version:1.1.150529
 * 更新：①私有属性变更到内部对象中
 *       ②增加setter,getter,has方法
 *       ③预加载设为同步，数据加载改为异步，doAjax方法bug修复和改进
 */
function Lazerloader(){

    var pro = Lazerloader.prototype;
    var env = this;
    var envattrs = new Object();

    /**
     * IE8下将console视为空函数(对象)
     * @type {Console}
     */
    window.console = window.console || (function(){
        var c = {}; c.log = c.warn = c.debug = c.info = c.error = c.time = c.dir = c.profile
            = c.clear = c.exception = c.trace = c.assert = function(){};
        return c;
    })();

    /**
     * 判断是否有属性
     *  null为特殊对象
     * @param key
     */
    pro.hasAttr = function(key){
        return envattrs[key] !== undefined ;
    };

    /**
     * 设置配置参数
     * @param key
     * @param val
     * @param force 是否强制设置,参数不存在的情况下
     * @returns {boolean}
     */
    pro.set = function (key,val,force) {
        if(env.hasAttr(key) || force === true ){
            envattrs[key] = val;
            return true;
        }
        return false;
    };
    /**
     * @param key
     * @returns mixed || undefined
     */
    pro.get = function (key) {
        return envattrs[key];
    };

    /**
     * 初始化
     * @param conf  初始化参数对象
     */
    pro.init = function (conf) {
        /*-- 初始化参数 --*/
    	//请求 地址
        envattrs.url = 'source.php';
        //请求连续模式，满足cur < total时自动请求，每次请求完毕后加上size大小的偏移
        envattrs.cur = 0;
        envattrs.size = 10;
        envattrs.total = 0;
        //预加载返回的数据
        envattrs.predata = null;
        //模板克隆依附的区域
        envattrs.ctnselector = 'body';
        //模板ID选择器
        envattrs.tplid = '';
        //延时设置过小可能导致并发访问量过大，浏览器和服务器都可能面临压力
        envattrs.delay = 100;
        //访问类型  pre-初次见面 com-之后的见面
        envattrs.tag = 'pre';
        //回调函数必须在之前定义或者在$(function(){})中定义
        //已加载完毕后回调，参数是返回的结果
        envattrs.precall = null;
        //预加载后的加载回调
        envattrs.loadcall = null;
        
        //私有以下划线开头,不该由用户配置
        envattrs._isfirst = true;
        envattrs._curtpl = null;

        for( var x in conf){
            //自带是否存在判断
            env.set(x, conf[x]);
        }
        //模板隐藏
        var tpl = $("#"+envattrs.tplid);
        tpl.length &&  tpl.css('display','none');
        return env;
    };

    /**
     * ajax加载函数
     * @param url  请求URL
     * @param senddata 发送的数据，对象
     * @param isasync 是否异步，默认为true，为true时返回值无意义
     * @param callback
     * @param param 回调的第二个参数   第一个参数是返回的数据
     * @returns {*}
     */
    pro.doAjax = function(url,senddata,isasync,callback,param){
        var dat = null;
        //console.log('looksend',senddata);
        senddata.curindex = envattrs.cur;
        senddata.size = envattrs.size;
        senddata.total = envattrs.total;
        $.ajax({
            type:'POST',
            url:envattrs.url,
            data:senddata,
            async:isasync===null?true:isasync,
            success: function (data) {
                dat = eval("("+data+")");
                if(callback!==null && callback !== undefined){
                    callback(dat,param);
                }
            }
        });
        return isasync?undefined:dat;
    };

    /**
     * 从该容器中克隆模板  子元素全部删除
     * @param tplid  模板的class属性
     * @param container  模板的包裹容器，用于缩小范围
     * @returns {*}
     */
    pro.getClone = function (tplselect,container,tag) {
        //.css('display','')可以使之使用原有的display属性
        var clone = container.find(tplselect).eq(0).clone().css('display','').css('height','auto').removeAttr('id');//高度统一为自己适应
        if(tag !== true){
            clone.html('');
        }
        return  clone;
    };

    /**
     *
     * @param key 键名称，对应模板中的class值，用于发现目标模板，
     * @param val 为基本值时，key所对应的class为单个元素的情况
     *              为对象或数组时，可以对应的class为内嵌元素的情况
     * @param container  如果目标为非内嵌元素的情况，
     */
    pro._goThrough = function (key,val,container){
        if(val instanceof  Object){
            //是数组或者对象(多元素)
            for(var x in val){
                if(isNaN(x)){
                    //字符串值 ：①模板层 ②替换层
                    //console.log(key,val,x,container);
                    if(val[x] instanceof Object){
                        //还在模板层，待扩展
                    	console.log(val[x]);
                    }else{
                        //到了替换层,将对象的属性遍历并替换到模板中
                        var newcontainer = env.getClone('.'+key,envattrs._curtpl,true);
                        for(var i in val){
                            //env._goThrough(key,val[i],newcontainer);
                            //console.log(i,val[i],newcontainer);
                            newcontainer.find("."+i).html(val[i]);
                        }
                        container.append(newcontainer);
                        break;
                    }
                }else{
                    //如果键是纯数字，按规定是模板层，当时下层一定是替换层，使用完整克隆
                    var newcontainer = env.getClone('.'+key,envattrs._curtpl,true);
                    if(val[x] instanceof Object ){
                        //键为数字的情况下val[x]必定为对象
                        for(var i in val[x]){
                            newcontainer.find('.'+i).html(val[x][i]);
                        }
                    }else{
                        //for(var j in val[x]){//遍历属性
                        //    env._goThrough(j,val[x][j],newcontainer);
                        //}
                    }
                    container.append(newcontainer); //newcontainer -> container
                }
            }
        }else{
            //如果是单元素就直接输出
            var newcontainer = env.getClone('.'+key,envattrs._curtpl).html(val);
            container.append(newcontainer);
        }
    };

    pro._loadTpl = function(tplid,data){
        for(var i=0; i < data.length; i++){
            envattrs._curtpl = $('#'+envattrs.tplid,$(envattrs.ctnselector));
            var curclone  = env.getClone('#'+envattrs.tplid,$(envattrs.ctnselector));
            for(var xlt in data[i]){
                //将值输出到模板的位置
                env._goThrough(xlt,data[i][xlt],curclone);
            }
            $( envattrs.ctnselector).append(curclone);
        }
    };

    /**
     * 加载服务器返回的数据，渲染到前端模板中
     * @param data 服务器返回的数据
     * @param param 用户自定义的参数，暂时未定义
     */
    pro.loadData = function(data,param){
        env._loadTpl(envattrs.tplid,data.data);
        if(envattrs.loadcall){
            envattrs.loadcall(data);
        }
    };

    /**
     * 自动加载数据
     * 按照系统设置的延时进行
     */
    pro.autoLoad = function () {
        if(envattrs._isfirst && envattrs.predata.length > 0){
            //立即加载第一次数据
            env.doAjax(envattrs.url,
            		{'tag':envattrs.tag,'restore':envattrs.predata.slice(envattrs.cur,envattrs.cur+envattrs.size)},
            		true,
            		env.loadData,
            		null);
            //env.loadData(env.doAjax(env.url,{'tag':env.tag,'keydata':env.predata.slice(env.cur,env.cur+env.size)},false,null,null));
            envattrs.cur += envattrs.size;
            envattrs._isfirst = false;
        }
        //第二次加载会在一定延时过后执行
        setTimeout(function(){
            if(envattrs.cur < envattrs.total){
                env.doAjax(envattrs.url,
	            		{'tag':envattrs.tag,
	                	'restore':envattrs.predata.slice(envattrs.cur,envattrs.cur+envattrs.size)},
	                	true,
	                	env.loadData,
	                	null);
                //env.loadData(env.doAjax(env.url,{'tag':env.tag,'keydata':env.predata.slice(env.cur,env.cur+env.size)},false,null,null));
                envattrs.cur += envattrs.size;
                return env.autoLoad();
            }
        },envattrs.delay);
    };

    /**
     * 获取预加载的数据
     * 预加载数据返回值：
     * 	①data 预加载返回值数据
     * 	②goon 是否继续加载数据，未设置或为真时会继续加载
     * @returns {boolean}
     */
    pro.preload = function (){
        var res = env.doAjax(envattrs.url,{'tag':envattrs.tag},false,null,null);
        //返回的数据用于查询标记
        if(res){
            envattrs.predata = res.data;
            envattrs.total = res.total || envattrs.predata.length;
            //加载预加载模板,预加载的数据显示在data属性中
            env._loadTpl(envattrs.tplid,res.data);
        }else{
            console.log(res);
            alert("can not get init param！");
            return false;
        }
        /*********** 预加载模板调用  待开发 **************/
        envattrs.tag = "com";
        //预加载完毕后回调自定义的函数
        if(envattrs.precall){
            envattrs.precall(res);
        }
        return res.goon === undefined || res.goon == true;
    };

    /**
     * 判断是否经过初始化
     *  通过判断是否初始化第一个参数，可以变更实现
     * @returns {*}
     */
    pro.hadInit = function(){
        return env.hasAttr("url");
    };

    /*开始执行执行*/
    pro.start = function(){
        //判断是否已经初始化
        if(!env.hadInit()){
        	alert("have inited?");
        }else{
            if(!env.preload()){
                alert('network exception!');
            }else{
                //console.log('preloadData',env.predata);
                env.autoLoad();
            }
        }
        return env;
    };
}

