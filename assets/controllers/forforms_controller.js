import { Controller } from 'stimulus';
import addLetterforms from './../js/addLetterforms';

export default class extends Controller {
    connect() {
        addLetterforms(3);
    }
}
