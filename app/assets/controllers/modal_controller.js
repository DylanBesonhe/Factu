import { Controller } from '@hotwired/stimulus';

/**
 * Controller pour les modals de confirmation
 *
 * Usage:
 * <div data-controller="modal">
 *   <button data-action="modal#open" data-modal-title="Confirmer la suppression" data-modal-message="Voulez-vous vraiment supprimer cet élément ?">
 *     Supprimer
 *   </button>
 *
 *   <template data-modal-target="template">
 *     <form action="/delete/1" method="post">
 *       <input type="hidden" name="_token" value="...">
 *     </form>
 *   </template>
 * </div>
 */
export default class extends Controller {
    static targets = ['template'];
    static values = {
        title: { type: String, default: 'Confirmation' },
        message: { type: String, default: 'Voulez-vous continuer ?' },
        confirmText: { type: String, default: 'Confirmer' },
        cancelText: { type: String, default: 'Annuler' },
        confirmClass: { type: String, default: 'danger' }
    };

    open(event) {
        event.preventDefault();

        const button = event.currentTarget;
        const title = button.dataset.modalTitle || this.titleValue;
        const message = button.dataset.modalMessage || this.messageValue;
        const confirmText = button.dataset.modalConfirmText || this.confirmTextValue;
        const cancelText = button.dataset.modalCancelText || this.cancelTextValue;
        const confirmClass = button.dataset.modalConfirmClass || this.confirmClassValue;

        // Récupérer le formulaire depuis le template ou les données du bouton
        let formHtml = '';
        if (this.hasTemplateTarget) {
            formHtml = this.templateTarget.innerHTML;
        } else if (button.dataset.modalFormAction) {
            const token = button.dataset.modalCsrfToken || '';
            formHtml = `
                <form action="${button.dataset.modalFormAction}" method="post">
                    <input type="hidden" name="_token" value="${token}">
                </form>
            `;
        }

        this.showModal(title, message, confirmText, cancelText, confirmClass, formHtml);
    }

    showModal(title, message, confirmText, cancelText, confirmClass, formHtml) {
        // Créer le backdrop
        const backdrop = document.createElement('div');
        backdrop.className = 'fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity z-50';
        backdrop.id = 'modal-backdrop';

        // Créer le modal
        const modal = document.createElement('div');
        modal.className = 'fixed inset-0 z-50 overflow-y-auto';
        modal.id = 'modal-container';
        modal.innerHTML = `
            <div class="flex min-h-full items-end justify-center p-4 text-center sm:items-center sm:p-0">
                <div class="relative transform overflow-hidden rounded-lg bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg">
                    <div class="bg-white px-4 pb-4 pt-5 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full ${confirmClass === 'danger' ? 'bg-red-100' : 'bg-blue-100'} sm:mx-0 sm:h-10 sm:w-10">
                                ${confirmClass === 'danger' ? `
                                    <svg class="h-6 w-6 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                                    </svg>
                                ` : `
                                    <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
                                    </svg>
                                `}
                            </div>
                            <div class="mt-3 text-center sm:ml-4 sm:mt-0 sm:text-left">
                                <h3 class="text-base font-semibold leading-6 text-gray-900">${title}</h3>
                                <div class="mt-2">
                                    <p class="text-sm text-gray-500">${message}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="hidden" id="modal-form-container">${formHtml}</div>
                    <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                        <button type="button" id="modal-confirm-btn" class="${confirmClass === 'danger' ? 'bg-red-600 hover:bg-red-500' : 'bg-blue-600 hover:bg-blue-500'} inline-flex w-full justify-center rounded-md px-3 py-2 text-sm font-semibold text-white shadow-sm sm:ml-3 sm:w-auto">
                            ${confirmText}
                        </button>
                        <button type="button" id="modal-cancel-btn" class="mt-3 inline-flex w-full justify-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 sm:mt-0 sm:w-auto">
                            ${cancelText}
                        </button>
                    </div>
                </div>
            </div>
        `;

        document.body.appendChild(backdrop);
        document.body.appendChild(modal);

        // Gérer les événements
        const confirmBtn = document.getElementById('modal-confirm-btn');
        const cancelBtn = document.getElementById('modal-cancel-btn');

        confirmBtn.addEventListener('click', () => {
            const form = document.querySelector('#modal-form-container form');
            if (form) {
                form.submit();
            }
            this.closeModal();
        });

        cancelBtn.addEventListener('click', () => this.closeModal());
        backdrop.addEventListener('click', () => this.closeModal());

        // Fermer avec Escape
        document.addEventListener('keydown', this.handleEscape.bind(this));
    }

    handleEscape(event) {
        if (event.key === 'Escape') {
            this.closeModal();
        }
    }

    closeModal() {
        const backdrop = document.getElementById('modal-backdrop');
        const modal = document.getElementById('modal-container');

        if (backdrop) backdrop.remove();
        if (modal) modal.remove();

        document.removeEventListener('keydown', this.handleEscape.bind(this));
    }
}
