class Xformhelper {
    static autoTrim(selector) {
        $(selector).change(e => {
            let $this = $(e.target)
            $this.val($this.val().trim())
        })
    }
}

$(() => {
    Xformhelper.autoTrim('.xform-autotrim')
})
