import { Controller } from '@hotwired/stimulus';
import addLetterforms from './../js/addLetterforms';

export default class extends Controller {
    connect() {
        addLetterforms(3);
    }
}
