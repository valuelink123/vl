function queryStringToObject(queryString = location.search.substr(1)) {

    let obj = {}
    let strs = queryString.split('&')

    for (let str of strs) {

        let pair = str.split('=')

        if (!pair[0]) continue

        let objRef = obj

        let paths = decodeURIComponent(pair[0]).split(/[\[\]]+/)

        let key = paths[0]

        if (paths.length > 1) {

            paths.pop()

            key = paths.pop()

            for (let path of paths) {
                if (!objRef[path]) {
                    objRef[path] = {}
                }
                objRef = objRef[path]
            }

        }

        objRef[key] = pair[1] ? decodeURIComponent(pair[1]) : ''

    }

    return obj
}

function objectToQueryString(obj) {

    let strs = []

    function cook(obj, prefix = '') {

        for (let key in obj) {

            let val = obj[key]
            key = prefix ? `${prefix}[${key}]` : key

            if (val instanceof Object) {
                cook(val, key)
            } else {
                key = encodeURIComponent(key)
                val = encodeURIComponent(val)
                strs.push(`${key}=${val}`)
            }
        }

    }

    cook(obj)

    return strs.join('&')
}

function objectFilte(obj, keys = [], except = true) {

    keys = new Set(keys)

    let result = {}

    for (let k in obj) {
        if (except) {
            if (keys.has(k)) continue
        } else {
            if (!keys.has(k)) continue
        }
        result[k] = obj[k]
    }

    return result
}

function rows2object(rows, keyFields, valueField = null) {

    let separator = (keyFields instanceof Array) ? keyFields.pop() : null

    let dataObject = {}

    for (let row of rows) {
        let key = (null === separator) ? row[keyFields] : keyFields.map(k => row[k]).join(separator)
        dataObject[key] = (null !== valueField) ? row[valueField] : row
    }

    return dataObject
}

/**
 * Ajax post data by json format
 *
 * @param url
 * @param data data object or form element
 */
function postByJson(url, data) {

    let $form = null

    if (data.jquery) {
        $form = data
    } else if (data instanceof HTMLFormElement) {
        $form = $(data)
    }

    if ($form) data = rows2object($form.serializeArray(), 'name', 'value')

    return new Promise((resolve, reject) => {
        $.ajax({
            url,
            method: 'POST',
            dataType: 'json',
            data: JSON.stringify(data),
            headers: {'Content-Type': 'application/json'},
            success(arr) {
                if (false === arr[0]) return reject(new Error(arr[1]))
                resolve(arr)
            },
            error(xhr, status, errmsg) {
                reject(new Error(errmsg))
            }
        })
    })
}

/**
 * @param params todo 自动提示
 */
function bindDelayEvents(...params) {

    if (params[3] instanceof Function) {
        var [capturEles, eTypes, eles, callback, delay = 76] = params
    } else if (params[2] instanceof Function) {
        var [eles, eTypes, callback, delay = 76] = params
    } else {
        throw new Error('Invalid callback')
    }

    let stid = 0

    function func(...args) {
        clearTimeout(stid) // 为了防止频繁执行设计的
        stid = setTimeout(callback.bind(this, ...args), delay)
    }

    // 既可以是数组，也可以是空格分隔的字符串
    (eTypes instanceof Array) || (eTypes = eTypes.split(/\s+/));

    if (capturEles) {

        if (capturEles instanceof Element) {
            capturEles = [capturEles]
        } else {
            capturEles = document.querySelectorAll(capturEles)
        }

        for (let capturEle of capturEles) {

            for (let eType of eTypes) {
                capturEle.addEventListener(eType, (e) => {

                    let pathSet = new Set(e.path)
                    let elesSet = new Set(capturEle.querySelectorAll(eles))

                    // 修改属性的私有、只读等限制
                    Object.defineProperty(e, 'currentTarget', {writable: true})
                    // Object.getOwnPropertyDescriptor(e, 'currentTarget')
                    // jQuery 自己定义了一个事件类，而且没有继承 Event 类
                    // 好处是和自带事件系统分离，两种方式互不影响

                    for (let ele of elesSet) {
                        // e.target.matches(eles)
                        if (pathSet.has(ele)) {
                            e.currentTarget = ele
                            // e.delegateTarget = capturEle
                            func.call(ele, e)
                            // event.stopPropagation();
                            // event.cancelBubble = bool;
                        }
                    }

                }, {capture: true}); // todo 不知道这个配置有啥用，能不能自动设置 currentTarget
            }

        }

    } else {

        (eles instanceof Element) && (eles = [eles]);
        (eles instanceof Array) || (eles = eles.split(','));

        for (let eType of eTypes) {
            for (let ele of eles) {
                (ele instanceof Element) || (ele = document.querySelector(ele));
                ele && ele.addEventListener(eType, func);
            }
        }

    }
}

/**
 * 选中文本
 */
function selectText(ele) {
    if (document.selection) {
        var range = document.body.createTextRange();
        range.moveToElementText(ele);
        range.select();
    } else if (window.getSelection) {
        window.getSelection().empty();
        var range = document.createRange();
        range.selectNodeContents(ele);
        window.getSelection().addRange(range);
    }
}

function getUrlFileName(url) {
    let ms = url.match(/([^/]+\.\w+)$/)
    let file = ms ? ms[1] : url
    return file
}
