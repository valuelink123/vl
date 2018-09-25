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

    function getQuerys() {
        let obj = {}
        if (location.search) {
            let strs = location.search.substr(1).split('&')
            for (let str of strs) {
                let par = str.split('=')
                obj[par[0]] = par[1] ? decodeURIComponent(par[1]) : ''
            }
        }
        return obj
    }
</script>