{
    function tplCompile(tpl) {

        tpl = tpl.replace(/<%([^]+?)%>/g, "`);$1;_push(`")

        return new Function(
            'vars',
            `let _output = []
             let _push = _output.push.bind(_output)
             eval(\`var {\${Object.keys(vars).join(",")}} = vars\`)
             _push(\`${tpl}\`)
             return _output.join("")`
        )
    }

    function tplRender(selector, vars) {

        if (!(selector instanceof Element)) {
            selector = document.querySelector(selector)
            if (!selector) return ''
        }

        if (!selector._compile) {
            selector._compile = tplCompile(selector.innerHTML)
        }

        return selector._compile(vars)
    }
}
