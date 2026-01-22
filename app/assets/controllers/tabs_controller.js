import { Controller } from '@hotwired/stimulus';

/**
 * Controller pour les onglets avec support clavier et animations
 *
 * Usage:
 * <div data-controller="tabs" data-tabs-default-value="infos">
 *   <div data-tabs-target="list" role="tablist">
 *     <button data-tabs-target="tab" data-tabs-id="infos" type="button">Informations</button>
 *     <button data-tabs-target="tab" data-tabs-id="contacts" type="button">Contacts</button>
 *   </div>
 *   <div data-tabs-target="panel" data-tabs-for="infos">Content 1</div>
 *   <div data-tabs-target="panel" data-tabs-for="contacts">Content 2</div>
 * </div>
 */
export default class extends Controller {
    static targets = ['tab', 'panel', 'list'];
    static values = {
        default: { type: String, default: '' }
    };

    connect() {
        this.initTabs();
        this.setupKeyboardNavigation();
    }

    initTabs() {
        // Active le premier onglet ou celui par défaut
        const defaultTab = this.defaultValue || this.tabTargets[0]?.dataset.tabsId;
        if (defaultTab) {
            this.activate(defaultTab);
        }
    }

    setupKeyboardNavigation() {
        if (this.hasListTarget) {
            this.listTarget.addEventListener('keydown', this.handleKeydown.bind(this));
        }
    }

    handleKeydown(event) {
        const tabs = this.tabTargets;
        const currentIndex = tabs.findIndex(tab => tab.getAttribute('aria-selected') === 'true');

        let newIndex;
        switch (event.key) {
            case 'ArrowLeft':
                newIndex = currentIndex > 0 ? currentIndex - 1 : tabs.length - 1;
                break;
            case 'ArrowRight':
                newIndex = currentIndex < tabs.length - 1 ? currentIndex + 1 : 0;
                break;
            case 'Home':
                newIndex = 0;
                break;
            case 'End':
                newIndex = tabs.length - 1;
                break;
            default:
                return;
        }

        event.preventDefault();
        const newTab = tabs[newIndex];
        this.activate(newTab.dataset.tabsId);
        newTab.focus();
    }

    select(event) {
        const tabId = event.currentTarget.dataset.tabsId;
        this.activate(tabId);
    }

    activate(tabId) {
        // Met à jour les onglets
        this.tabTargets.forEach(tab => {
            const isActive = tab.dataset.tabsId === tabId;
            tab.setAttribute('aria-selected', isActive);
            tab.setAttribute('tabindex', isActive ? '0' : '-1');

            if (isActive) {
                tab.classList.add('border-blue-500', 'text-blue-600');
                tab.classList.remove('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            } else {
                tab.classList.remove('border-blue-500', 'text-blue-600');
                tab.classList.add('border-transparent', 'text-gray-500', 'hover:text-gray-700', 'hover:border-gray-300');
            }
        });

        // Met à jour les panneaux
        this.panelTargets.forEach(panel => {
            const isActive = panel.dataset.tabsFor === tabId;
            panel.hidden = !isActive;
            panel.setAttribute('aria-hidden', !isActive);

            if (isActive) {
                panel.classList.add('animate-fadeIn');
            }
        });
    }
}
