import { Controller } from 'stimulus';
import addLetterforms from './../js/addLetterforms';

export default class extends Controller {
    connect() {
        this.element.textContent = 'forforms Stimulus! Edit me in assets/controllers/hello_controller.js';
        addLetterforms(2);
    }
}
