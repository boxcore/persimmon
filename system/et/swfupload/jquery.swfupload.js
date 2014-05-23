;

     

(function($) {
    
    var _SWFOBJECT = [];


    var uploadStatus = {
        data: {},
        set: function(i, ii, status) {
            if (typeof (this.data[i]) === 'object') {
                this.data[i][ii] = status;
            } else {
                this.data[i] = {};
                this.data[i][ii] = {};
            }
        },
        get: function(i, ii) {
            if (typeof (this.data[i]) === 'object') {
                if (typeof (this.data[i][ii]) === 'object') {
                    return this.data[i][ii];
                }
            }
        },
        remove: function(i, ii) {
            if (typeof (this.data[i]) === 'object') {
                if (typeof (this.data[i][ii]) === 'object') {
                    delete this.data[i][ii];
                }
            }
        }
    };
    
    
    var _COOKIE = {
    "get" : function(cookie_name) {
        var arr = document.cookie.match( new RegExp("(^| )"+cookie_name+"=([^;]*)(;|$)") );
        return ( arr != null ) ? decodeURIComponent(arr[2]) : null;
    },
    "set" : function(cookie_name, value, exp, path, domain) {
        var exp    = typeof(exp) != "undefined" ? parseInt(exp) : 3600000*24;
        var path   = typeof(path) != "undefined" ? path : '/';
        var domain = typeof(domain) != "undefined" ? domain : window.location.domain;
        var date   = new Date();
        date.setTime(date.getTime() + exp);
        document.cookie = cookie_name + "=" + encodeURIComponent(value) + ";" +
                           "expires=" + date.toGMTString() + ";" +
                           "path=" + path;/* + ";" +
                           "domain=" + domain;*/
    },
    "delete" : function(cookie_name) {
        var date = new Date();
        var cval = this.get(cookie_name);
        date.setTime( parseInt(date.getTime()) - 3600 );
        if( cval != null ) {
            document.cookie = cookie_name + "="+cval+";expires="+date.toGMTString();
        }
    }
    };
   
 // for   debug
//    setInterval(function(){
//                  var date = new Date();
//                var dateStr = date.getHours() + ":" + date.getMinutes() + ":" + date.getSeconds();
//                console.log(dateStr +" SID " + _COOKIE.get(__SESSID_NAME));
//    },10000)
   
    function getPath() {
        var js = document.scripts || $('script'), jsPath = js[js.length - 1].src;
        return jsPath.substring(0, jsPath.lastIndexOf("/") + 1);
    }

    var _CURRENT_URL = getPath();

    function loadCss() {
        var head = $('head')[0], link = document.createElement("link");
        link.setAttribute('type', 'text/css');
        link.setAttribute('rel', 'stylesheet');
        link.setAttribute('href', _CURRENT_URL + 'style.css');
        head.appendChild(link);
    }

    $(function() {
        loadCss();
    });

    function FileProgress(file, targetID, setting) {
        this.swfSetting = setting;
        this.fileProgressID = file.id;
        this.complete = false;
        this.opacity = 100;
        this.height = 0;

        this.fileProgressWrapper = document.getElementById(this.fileProgressID);
        if (!this.fileProgressWrapper) {
            //图片容器
            this.fileProgressWrapper = document.createElement("li");
            this.fileProgressWrapper.className = "swfupload-li";
            this.fileProgressWrapper.id = this.fileProgressID;

            this.fileProgressWrapper.style.width = this.swfSetting.liWH[0];
            this.fileProgressWrapper.style.height = this.swfSetting.liWH[1];
            this.fileProgressElement = document.createElement("span");
            this.fileProgressElement.className = "swfupload-img-content";
            // this.fileProgressElement.innerHTML = file.name;

            //取消上传的按钮
            var progressCancel = document.createElement("a");
            progressCancel.className = "swfupload-del ";
            progressCancel.href = "javascript:void(0)";
            progressCancel.title = "删除";
            progressCancel.innerHTML = " ";

            if (!this.swfSetting.isShowDelete) {
                progressCancel.style.display = "none";
            }
            progressCancel.appendChild(document.createTextNode(" "));


            //进度条
            var progressBar = document.createElement("div");
            progressBar.className = "swfupload-status-bar";
            progressBar.visible = 'hidden';
            var progressStatus = document.createElement("span");
            progressStatus.appendChild(document.createTextNode(" "));
            progressStatus.style.width = "0%";
            progressBar.appendChild(progressStatus);

            this.fileProgressWrapper.appendChild(this.fileProgressElement);
            this.fileProgressWrapper.appendChild(progressCancel);
            this.fileProgressWrapper.appendChild(progressBar);

            var clEl = document.createElement("div");
            clEl.className = "clb";
            this.fileProgressWrapper.appendChild(clEl);

            $(this.fileProgressWrapper).insertBefore(this.fileProgressWrapper);

            $(this.fileProgressWrapper).insertBefore(document.getElementById(targetID));

        } else {
            this.fileProgressElement = this.fileProgressWrapper.firstChild;
            //this.reset();
        }

        this.height = this.fileProgressWrapper.offsetHeight;
        this.fileProgressWrapper.title = file.name;
        this.setTimer(null);
    }

    FileProgress.prototype.setTimer = function(timer) {
        this.fileProgressElement["FP_TIMER"] = timer;
    };
    FileProgress.prototype.getTimer = function(timer) {
        return this.fileProgressElement["FP_TIMER"] || null;
    };

    FileProgress.prototype.reset = function() {
        this.fileProgressElement.childNodes[2].firstChild.style.width = "0%";
        this.appear();
    };

    FileProgress.prototype.setProgress = function(percentage) {
        var o = this.fileProgressWrapper.childNodes[2].firstChild;
        o.style.width = percentage + "%";
        //  o.innerHTML = percentage + "%";
        this.appear();
    };
    FileProgress.prototype.setComplete = function(data) {

        this.fileProgressElement.innerHTML = "<img src=" + data.url + ">";

        $("#SWFUpload_" + this.swfSetting.index).css("margin-left", "0");

        this.fileProgressWrapper.childNodes[2].style.display = 'none';
        this.complete = true;
    };
    FileProgress.prototype.setError = function() {
        this.fileProgressElement.className = "progressContainer red";
        this.fileProgressElement.childNodes[3].className = "progressBarError";
        this.fileProgressElement.childNodes[3].style.width = "";

        var oSelf = this;
        this.setTimer(setTimeout(function() {
            oSelf.disappear();
        }, 5000));
    };
    FileProgress.prototype.setCancelled = function(obj) {
        this.remove(obj);
    };
    FileProgress.prototype.setStatus = function(status) {
        this.fileProgressWrapper.childNodes[2].title = status;
    };
    FileProgress.prototype.remove = function(swfUploadInstance) {





        this.fileProgressWrapper.childNodes[1].style.display = "none";
        oSelf = this;
        var fileID = this.fileProgressID;

        uploadStatus.remove(swfUploadInstance.settings.index, fileID);

        if (swfUploadInstance) {
            swfUploadInstance.setFileUploadLimit(swfUploadInstance.settings.file_upload_limit + 1);
            swfUploadInstance.cancelUpload(fileID);
        }
        //delete
        $("#SWFUpload_" + swfUploadInstance.customSettings.index).css("margin-left", "0");
        //删除事件
         
        swfUploadInstance.customSettings.onDelete(fileID);
        

        swfUploadInstance.customSettings.alreadyUploadPic--;

        this.setTimer(setTimeout(function() {
            oSelf.disappear();
        }, 100));
    };

//Show/Hide the cancel button
    FileProgress.prototype.toggleCancel = function(show, swfUploadInstance) {
        if (swfUploadInstance) {
            var complete = this.complete;
            var self = this;
            this.fileProgressWrapper.childNodes[1].onclick = function() {
                self.remove(swfUploadInstance);
                return false;
            };
        }
    };


    FileProgress.prototype.appear = function() {
        if (this.getTimer() !== null) {
            clearTimeout(this.getTimer());
            this.setTimer(null);
        }

        if (this.fileProgressWrapper.filters) {
            try {
                this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = 100;
            } catch (e) {
                // If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
                this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=100)";
            }
        } else {
            this.fileProgressWrapper.style.opacity = 1;
        }

        // this.fileProgressWrapper.style.height = "";

        this.height = this.fileProgressWrapper.offsetHeight;
        this.opacity = 100;
        this.fileProgressWrapper.style.display = "";

    };

//Fades out and clips away the FileProgress box.
    FileProgress.prototype.disappear = function() {
        var reduceOpacityBy = 15;
        var reduceHeightBy = 4;
        var rate = 30;	// 15 fps

        if (this.opacity > 0) {
            this.opacity -= reduceOpacityBy;
            if (this.opacity < 0) {
                this.opacity = 0;
            }

            if (this.fileProgressWrapper.filters) {
                try {
                    this.fileProgressWrapper.filters.item("DXImageTransform.Microsoft.Alpha").opacity = this.opacity;
                } catch (e) {
                    // If it is not set initially, the browser will throw an error.  This will set it if it is not set yet.
                    this.fileProgressWrapper.style.filter = "progid:DXImageTransform.Microsoft.Alpha(opacity=" + this.opacity + ")";
                }
            } else {
                this.fileProgressWrapper.style.opacity = this.opacity / 100;
            }
        }

        if (this.height > 0) {
            this.height -= reduceHeightBy;
            if (this.height < 0) {
                this.height = 0;
            }

            this.fileProgressWrapper.style.height = this.height + "px";
        }

        if (this.height > 0 || this.opacity > 0) {
            var oSelf = this;
            this.setTimer(setTimeout(function() {
                oSelf.disappear();
            }, rate));
        } else {
            this.fileProgressWrapper.parentNode.removeChild(this.fileProgressWrapper);
            this.setTimer(null);
        }
    };


    $.swfuploadCloseli = function(obj, index) {
        $(obj).parent("li").remove();

        //上传成功减1
        _SWFOBJECT[index].setFileUploadLimit(_SWFOBJECT[index].settings.file_upload_limit + 1);
        _SWFOBJECT[index].setFileQueueLimit(_SWFOBJECT[index].settings.file_upload_limit + 1);

        _SWFOBJECT[index].customSettings.alreadyUploadPic--;
         _SWFOBJECT[index].customSettings.onDelete();
        $("#SWFUpload_" + setting.index).css("margin-left", "0");

    };

    var image = {
        init: function(obj, setting) {

            var defaltHtml = '';
            var delHtml = "";

            if (setting.isShowDelete) {
                delHtml = '<a class="swfupload-del " href="javascript:" onclick="$.swfuploadCloseli(this,' + setting.index + ')" title="删除">  </a>';
            }

            if (typeof (setting.defaultValue) === 'string' && setting.defaultValue) {
                setting.defaultValue = [setting.defaultValue];
            }

            var inputUrl = '', inputValue = '', inputName = '', data = '', tmp = '';
            for (d in setting.defaultValue) {
                if (typeof (setting.defaultValue[d]) === 'string') {
                    inputName = setting.input;
                    inputUrl = inputValue = setting.defaultValue[d];
                } else {
                    inputValue = setting.defaultValue[d].value;
                    inputUrl = setting.defaultValue[d].url || setting.defaultValue[d].value;
                    inputName = setting.defaultValue[d].input || setting.input;
                }
                data = "<span class='swfupload-img-content'><img src='" + inputUrl + "'></span>";

                if (typeof (setting.before) === 'function') {
                    tmp = setting.before(setting.defaultValue[d]);
                    if (tmp) {
                        data += tmp;
                    }
                } else {
                    data += "<input type='hidden' name='" + inputName + "' value='" + inputValue + "'>";
                }

                defaltHtml += '<li class="swfupload-li" style="width:' + setting.liWH[0] + ';height:' + setting.liWH[1] + '">' + delHtml + data + '</li>';

                setting.alreadyUploadPic++;
            }
            var html = '<ul id="swfupload-ul-' + setting.index + '" class="' + setting.className + '">' +
                    defaltHtml
                    + '<li id="swfupload-li-' + setting.index + '" class="swfupload-li-btn" style="width:' + ((setting.btnWH && setting.btnWH[0]) || setting.liWH[0]) + ';height:' + ((setting.btnWH && setting.btnWH[1]) || setting.liWH[1]) + ';padding:0;overflow:hidden;position:relative">' +
                    '<span id="spanButtonPlaceholder-' + setting.index + '"></span>' +
                    '</li></ul>';
            obj.html(html);



            this.createFlashUpload(setting);

        },
        upload: function(o) {

            var ext = /\.[^\.]+$/.exec($(o).val());
            ext = ext.toString().replace('.', '');
            if (jQuery.inArray(ext.toLowerCase(), allowext) === -1) {
                alert('只允许上' + allowext.join(",") + '传格式的文件');
                return false;
            }
            return false;
        },
        createFlashUpload: function(setting) {
            
            var allowType = new Array();
            for (var i in setting.allowext)
                allowType[i] = "*." + setting.allowext[i];
            allowType = allowType.join(";");
            _SWFOBJECT[_SWFOBJECT.length] = new SWFUpload({
                // Backend Settings
                upload_url: setting.uploadUrl,
                post_params: setting.postParams,
                // File Upload Settings
                file_size_limit: setting.fileSize,
                file_types: allowType,
                file_types_description: "All Files", //"JPEG Images;GIF Images;JPG Images;PNG Image",
                file_upload_limit: setting.max === 1 ? 0 : (setting.alreadyUploadPic >= setting.max ? 1 : setting.max - setting.alreadyUploadPic), //最大上传数量
                file_queue_limit: setting.max === 1 ? 1 : setting.fileQeueLimit,
                file_post_name: setting.filePostName,
                // Event Handler Settings - these functions as defined in Handlers.js
                //  The handlers are not part of SWFUpload but are part of my website and control how
                //  my website reacts to the SWFUpload events.
                swfupload_preload_handler: this.preLoad,
                swfupload_load_failed_handler: this.loadFailed,
                file_queue_error_handler: this.fileQueueError,
                file_dialog_complete_handler: this.fileDialogComplete,
                file_queued_handler: this.fileQueued,
                upload_progress_handler: this.uploadProgress,
                upload_error_handler: this.uploadError,
                upload_success_handler: this.uploadSuccess,
                upload_complete_handler: this.uploadComplete,
                // Button Settings
                button_image_url: setting.btnImage,
                button_placeholder_id: "spanButtonPlaceholder-" + setting.index,
                button_width: setting.width,
                button_height: setting.height,
                button_text: '', //<span class="button">选择1张或多张图</span>
                button_text_style: '.button {font:12px Arial,Helvetica,sans-serif,Simsun;}',
                button_text_top_padding: 59,
                button_text_left_padding: 15,
                button_window_mode: SWFUpload.WINDOW_MODE.TRANSPARENT,
                button_cursor: SWFUpload.CURSOR.HAND,
                // Flash Settings
                flash_url: _CURRENT_URL + "swfupload.swf",
                flash9_url: _CURRENT_URL + "swfupload_fp9.swf",
                prevent_swf_caching: false,
                preserve_relative_urls: false,
                custom_settings: {
                    input: setting.input,
                    progressTarget: 'swfupload-li-' + setting.index,
                    callback: setting.callback,
                    before: setting.before,
                    index: setting.index,
                    allowUploadPic: setting.max, //允许上传多少
                    liWH: setting.liWH,
                    alreadyUploadPic: setting.alreadyUploadPic,
                    isShowDelete: setting.isShowDelete,
                    isMaxHideBtn: setting.isMaxHideBtn,
                    onDelete: setting.onDelete
                },
                // Debug Settings
                debug: false
            });

            if (setting.max <= setting.alreadyUploadPic && setting.isMaxHideBtn) {
                $("#SWFUpload_" + setting.index).css("margin-left", "300px");
            }

        },
        fileQueued: function(file) {
            
            //处理cookie问题
            if( __SESSID_POST_NAME && __SESSID_NAME){
                this.addPostParam(__SESSID_POST_NAME, _COOKIE.get(__SESSID_NAME));
               // console.log("before submit " + _COOKIE.get(__SESSID_NAME));
            }
            
            try {
                var progress = new FileProgress(file, this.customSettings.progressTarget, this.customSettings);
                progress.setStatus("Pending...");
                progress.toggleCancel(true, this);
                //状态
                uploadStatus.set(this.customSettings.index, file.id, false);


                //只上传一个
                if (this.customSettings.allowUploadPic === 1) {
                    $("#" + file.id).prev("li").remove();
                }


            } catch (ex) {
                alert(ex);
            }
        },
        removeImage: function() {

        },
        stopAjax: function() {
            var stopUploadPic = 1;
        },
        preLoad: function() {
            if (!this.support.loading) {
                alert("You need the Flash Player to use SWFUpload.");
                return false;
            } else if (!this.support.imageResize) {
                alert("You need Flash Player 10 to upload resized images.");
                return false;
            }
        },
        loadFailed: function() {
            alert('抱歉，加载上传组件出错,请刷新再试或者联系管理员');
        },
        fileQueueError: function(file, errorCode, message) {
            try {
                switch (errorCode) {
                    case SWFUpload.QUEUE_ERROR.QUEUE_LIMIT_EXCEEDED:
                        alert("同时上传文件数超过限制,还能上传" + message + "个");
                        return;
                    case SWFUpload.QUEUE_ERROR.FILE_EXCEEDS_SIZE_LIMIT:
                        alert('文件上传过大，请上传小于' + fileSize);
                        return;
                    case SWFUpload.QUEUE_ERROR.ZERO_BYTE_FILE:
                        alert('不允许上传空字节文件');
                        return;
                    case SWFUpload.QUEUE_ERROR.INVALID_FILETYPE:
                        alert('上传文件类型不允许');
                        return;
                    default:
                        alert('上传过程出现失败，请联系管理员');
                        return;
                }
            } catch (e) {
                alert(e);
                // ui.error(e);
            }

        },
        fileDialogComplete: function(numFilesSelected, numFilesQueued) {
            try {
                if (numFilesQueued) {
                    this.startUpload();
                }
            } catch (ex) {
                alert(e);
            }
        },
        uploadProgress: function(file, bytesLoaded) {
            try {
                var percent = Math.ceil((bytesLoaded / file.size) * 100);

                var progress = new FileProgress(file, this.customSettings.upload_target, this.customSettings);
                progress.setProgress(percent);
                progress.setStatus("Uploading...");
                progress.toggleCancel(true, this);
            } catch (ex) {
                alert(ex);
                // ui.error(ex);
            }
        },
        uploadError: function(file, errorCode, message) {
            try {
                switch (errorCode) {
                    case SWFUpload.UPLOAD_ERROR.HTTP_ERROR:
                        alert("Error Code: HTTP Error, File name: " + file.name + ", Message: " + message);
                        break;
                    case SWFUpload.UPLOAD_ERROR.UPLOAD_FAILED:
                        alert("Error Code: Upload Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                        break;
                    case SWFUpload.UPLOAD_ERROR.IO_ERROR:
                        alert("Error Code: IO Error, File name: " + file.name + ", Message: " + message);
                        break;
                    case SWFUpload.UPLOAD_ERROR.SECURITY_ERROR:
                        alert("Error Code: Security Error, File name: " + file.name + ", Message: " + message);
                        break;
                    case SWFUpload.UPLOAD_ERROR.UPLOAD_LIMIT_EXCEEDED:
                        alert('抱歉，超过上传次数限制' + this.settings.file_upload_limit + '次');
                        break;
                    case SWFUpload.UPLOAD_ERROR.FILE_VALIDATION_FAILED:
                        alert("Error Code: File Validation Failed, File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                        break;
                    case SWFUpload.UPLOAD_ERROR.FILE_CANCELLED:
                        if (this.getStats().files_queued === 0) {
                            // document.getElementById(this.customSettings.cancelButtonId).disabled = true;
                        }
                        //  progress.setStatus("Cancelled");
                        //   progress.setCancelled(this);
                        break;
                    case SWFUpload.UPLOAD_ERROR.UPLOAD_STOPPED:
                        //   progress.setStatus("Stopped");
                        break;
                    default:
                        alert("Error Code: " + errorCode + ", File name: " + file.name + ", File size: " + file.size + ", Message: " + message);
                        break;
                }
            } catch (ex) {
                alert(ex);
            }
        },
        uploadSuccess: function(file, serverData) {
            var that = this;
            var left = 0;
           // console.log(serverData);
            var json = eval('(' + serverData + ')');
            var progress = new FileProgress(file, this.customSettings.upload_target, this.customSettings);
            this.customSettings.alreadyUploadPic++;

            if (json.error === '') {
                progress.setComplete(json);
                progress.toggleCancel(true, this);
                
                //set new cookie id
                if( __SESSID_NAME &&  __SESSID_POST_NAME && json[__SESSID_POST_NAME]){
                   // _COOKIE.set(__SESSID_NAME, json[__SESSID_POST_NAME]);
                    //console.log("set new SID",json[__SESSID_POST_NAME]);
                }

                if (typeof (this.customSettings.callback) === 'function') {
                    this.customSettings.callback(json, file.id);
                } else {
                    $("#" + file.id).append("<input name='" + this.customSettings.input + "' value='" + json.url + "' type='hidden'/>");
                }

                if (this.customSettings.allowUploadPic <= this.customSettings.alreadyUploadPic && this.customSettings.isMaxHideBtn) {
                    $("#SWFUpload_" + this.customSettings.index).css("margin-left", '300px');
                }

                uploadStatus.set(this.customSettings.index, file.id, true);

            } else {
                alert(json.error);
                progress.remove(this);
               if (this.customSettings.allowUploadPic === 1) {
                   this.setFileUploadLimit(100);
               }

            }
//    this.requeueUpload()
        },
        uploadComplete: function(file) {
            try {
                /*  I want the next upload to continue automatically so I'll call startUpload here */
                if (this.getStats().files_queued > 0) {
                    this.startUpload(this.getFile(0).ID);
                }
            } catch (ex) {
                alert(ex);
            }
        }
    };


    var setting = {
        uploadUrl: "", //上传路径
        postParams: {}, //post 到php传递的参数
        callback: null, //上传完后的回调 function(json,fileId)
        before: null, //初始化数据前的回调 有初始 defaultValue 才执行 用于方式图片初始值  function(参数为defaultValue)  返回字符串
        max: 10, //允许上传文件个数
        input: "", //input表单名
        defaultValue: [], // 初始数据  格式 [{input:'表单名空则使用 setting的input值',value:'值',url,'地址 地址为空则使用value值'}]


        height: 100, //按钮高度
        width: 100, //按钮宽度

        fileSize: "1 MB", //允许文件大小
        allowext: ['jpg', 'jpeg'], //允许文件格式
        className: 'swfupload-ul', //样式名 ul的样式名
        liWH: ['100px', '100px'], // li 的宽高  像 100px
        filePostName: 'imgFile',
        btnImage: _CURRENT_URL + 'upload-btn.jpg',
        fileQeueLimit: 10, // 允许一次上传几个,
        onDelete : function(){}, //删除时执行的回调
        alreadyUploadPic: 0,
        isShowDelete: true, //是否显示删除图片
        isMaxHideBtn: false, //达到上传数量后是否隐藏上传按钮
        index: 0
    };

    $.swfuploadSetting = function(options) {
        $.extend(setting, options);
    };

    $.fn.swfupload = function(options) {
        $(this).each(function() {

            $.extend(setting, options);

            setting.index = _SWFOBJECT.length;

            (function(obj, setting) {
                image.init(obj, setting);
            })($(this), setting);
        });
    };

    //上传状态
    $.extend({
        swfuploadInfo: function(index) {
            var swfuploadInfo = {};
            //总数
            swfuploadInfo.total = 0;
            //是否全部完成
            swfuploadInfo.complete = true;
            var data = uploadStatus.data;

            if (typeof (index) === 'undefined') {
                for (var index in data) {
                    for (var i in data[index]) {
                        swfuploadInfo.total++;
                        if (data[index][i] === false) {
                            //是否全部上传完
                            swfuploadInfo.complete = false;
                        }
                    }
                }
            } else {
                if (typeof (data[index]) === 'object') {
                    for (var i in data[index]) {
                        swfuploadInfo.total++;
                        if (data[index][i] === false) {
                            //是否全部上传完
                            swfuploadInfo.complete = false;
                        }
                    }
                }
            }

            return swfuploadInfo;
        }
    });

})(jQuery);
