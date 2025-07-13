// script_user.js - Version optimisée pour votre architecture
document.addEventListener('DOMContentLoaded', function() {
    // Cache DOM
    const navLinks = document.querySelectorAll('.nav-link');
    const mainContent = document.querySelector('.main-content');
    const sidebarContent = document.querySelector('.sidebar');

    // Fonction principale de chargement
    const loadPageContent = async (url, updateHistory = true) => {
        try {
            // Feedback visuel pendant le chargement
            mainContent.innerHTML = '<div class="loader"><i class="fas fa-spinner fa-spin"></i> Chargement...</div>';
            
            const response = await fetch(url);
            if (!response.ok) throw new Error('Erreur réseau');
            
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');

            // Extraction des contenus
            const newMainContent = doc.querySelector('.main-content').innerHTML;
            const newSidebarContent = doc.querySelector('.sidebar')?.innerHTML || '';

            // Mise à jour progressive
            requestAnimationFrame(() => {
                if (newSidebarContent) sidebarContent.innerHTML = newSidebarContent;
                mainContent.innerHTML = newMainContent;
                
                // Mise à jour du menu actif
                navLinks.forEach(link => {
                    link.classList.toggle('active', link.getAttribute('href') === url);
                });

                // Gestion du scroll
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });

            if (updateHistory) {
                history.pushState({ url }, '', url);
            }
        } catch (error) {
            console.error('Erreur:', error);
            mainContent.innerHTML = `
                <div class="error-message">
                    <i class="fas fa-exclamation-triangle"></i>
                    <p>Erreur de chargement. <a href="${url}">Réessayer</a></p>
                </div>
            `;
        }
    };

    // Gestion des clics navigation
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            loadPageContent(this.getAttribute('href'));
        });
    });

    // Gestion du retour navigateur
    window.addEventListener('popstate', (e) => {
        if (e.state?.url) {
            loadPageContent(e.state.url, false);
        }
    });

    // Délégation des événements pour le contenu dynamique
    document.addEventListener('click', function(e) {
        // Exemple: Gestion des cartes cliquables
        if (e.target.closest('.card:not(.no-link)')) {
            const card = e.target.closest('.card');
            if (!e.target.closest('a, button')) {
                const link = card.querySelector('a');
                if (link) {
                    e.preventDefault();
                    loadPageContent(link.getAttribute('href'));
                }
            }
        }
        
        // Gestion des formulaires en AJAX
        if (e.target.closest('form.ajax-form')) {
            e.preventDefault();
            const form = e.target.closest('form');
            // Ici vous pourriez ajouter la logique de soumission AJAX
        }
    });

    // Initialisation
    if (history.state?.url) {
        loadPageContent(history.state.url, false);
    } else {
        // Marquer la page active initiale
        const initialActiveLink = document.querySelector('.nav-link.active');
        if (initialActiveLink) {
            history.replaceState(
                { url: initialActiveLink.getAttribute('href') },
                '',
                initialActiveLink.getAttribute('href')
            );
        }
    }
});