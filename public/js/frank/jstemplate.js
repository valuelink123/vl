{
    let map = (ref, func) => {
        let arr = []
        for (let n in ref) {
            arr.push(func(ref[n], n))
        }
        return arr.join('')
    }

    function tplCompile(tpl) {

        // for-loop support
        tpl = tpl.replace(
            /\${for ([$,\w\s]+?) of ([$\w\s]+?)}/ig,
            '${map($2,($1)=>`'
        ).replace(
            /\${endfor}/ig,
            '`)}'
        )

        return `\`${tpl}\``
    }

    function tplRender(selector, vars) {

        if (!(selector instanceof Element)) {
            selector = document.querySelector(selector)
            if (!selector) return ''
        }

        // tpl cache
        let tpl = selector._tpl || (selector._tpl = tplCompile(selector.innerHTML))

        // extract vars
        for (let n in vars) {
            eval(`var ${n}=vars.${n}`)
        }

        return eval(tpl)
    }
}
