import {Event} from "../Event"

export default class extends Event {
    labels = []
    color = 'gray'
    app = 'var-dump'

    get type() {
        return 'var-dump'
    }

    get payloads() {
        return this.event.payload
    }

    get origin() {
        return {
            file: this.event.context.source.file,
            name: this.event.context.source.name,
            line_number: this.event.context.source.line,
        }
    }
}
