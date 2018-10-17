{
    let map = (ref, func) => {
        let arr = []
        for (let n in ref) {
            arr.push(func(ref[n], n))
        }
        return arr.join('')
    }

    function tplCompile(tpl, vars) {

        // for looping support
        do {
            var tpl_ = tpl.replace(
                /\${for ([$,\w\s]+?) of ([$\w\s]+?)}([^]+)\${endfor}/ig,
                '${map($2,($1)=>`$3`)}'
            )
            // nesting support
        } while (tpl !== tpl_ && (tpl = tpl_))

        // extract vars
        for (let n in vars) {
            eval(`var ${n}=vars.${n}`)
        }

        return eval(`\`${tpl}\``)
    }

    function tplRender(selector, vars) {
        (selector instanceof Element) || (selector = document.querySelector(selector));
        return tplCompile(selector ? selector.innerHTML : '', vars)
    }
}
