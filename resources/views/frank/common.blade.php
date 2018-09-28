<script>
    try {
        eval('()=>{}')
    } catch (e) {
        var error = 'This application requires the latest Google Chrome.'
        confirm(error) && (location = 'https://www.google.com/chrome/')
    }

    $.ajaxSetup({
        headers: {'X-CSRF-TOKEN': '{!! csrf_token() !!}'}
    })
</script>
<script>
    function queryStringToObject(queryString = location.search.substr(1)) {
        let obj = {}
        let strs = queryString.split('&')

        for (let str of strs) {
            let par = str.split('=')
            if (!par[0]) continue
            obj[par[0]] = par[1] ? decodeURIComponent(par[1]) : ''
        }

        return obj
    }

    function objectToQueryString(obj) {
        let strs = []
        for (let key in obj) {
            let val = encodeURIComponent(obj[key])
            strs.push(`${key}=${val}`)
        }
        return strs.join('&')
    }

    function objectFilte(obj, keys = [], except = true) {

        let map = {}

        for (let key of keys) {
            map[key] = true
        }

        let result = {}

        for (let k in obj) {
            if (except) {
                if (map[k]) continue
            } else {
                if (!map[k]) continue
            }
            result[k] = obj[k]
        }

        return result
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
</script>