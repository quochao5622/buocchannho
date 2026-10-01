<div
    wire:ignore
    style="display: none"
    x-data="{
        intervalMs: {{ (int) $minutes }} * 60 * 1000,
        dirtySince: null,
        lastRemindedAt: null,
        form: null,
        timer: null,
        markDirty: null,
        onVisible: null,
        init() {
            this.form = this.$el.closest('form') ?? document
            this.markDirty = () => { this.dirtySince ??= Date.now() }
            this.onVisible = () => { if (document.visibilityState === 'visible') this.check() }

            this.form.addEventListener('input', this.markDirty)
            this.form.addEventListener('change', this.markDirty)
            document.addEventListener('visibilitychange', this.onVisible)
            this.timer = setInterval(() => this.check(), 30000)
        },
        check() {
            if (! this.dirtySince) return
            if (Date.now() - (this.lastRemindedAt ?? this.dirtySince) < this.intervalMs) return

            this.lastRemindedAt = Date.now()

            new FilamentNotification()
                .title(@js($title))
                .body(@js($body).replace(':minutes', Math.floor((Date.now() - this.dirtySince) / 60000)))
                .warning()
                .duration(15000)
                .send()
        },
        destroy() {
            clearInterval(this.timer)
            this.form.removeEventListener('input', this.markDirty)
            this.form.removeEventListener('change', this.markDirty)
            document.removeEventListener('visibilitychange', this.onVisible)
        },
    }"
></div>
