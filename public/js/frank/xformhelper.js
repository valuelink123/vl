class Xformhelper {
    static autoTrim(selector) {
        $(selector).keyup(e => {
            let $this = $(e.target)
            $this.val($this.val().trim())
        })
    }
}

$(() => {
    Xformhelper.autoTrim('.xform-autotrim')
})
