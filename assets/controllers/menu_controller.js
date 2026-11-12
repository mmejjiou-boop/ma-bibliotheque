import { Controller } from '@hotwired/stimulus';

export default class extends Controller {
    static targets = ['panneau', 'ouvrir', 'fermer'];

    basculer() {
        const ouvert = this.panneauTarget.classList.toggle('hidden') === false;

        this.ouvrirTarget.classList.toggle('hidden', ouvert);
        this.fermerTarget.classList.toggle('hidden', !ouvert);
        this.element.querySelector('[aria-controls]').setAttribute('aria-expanded', ouvert);
    }
}
