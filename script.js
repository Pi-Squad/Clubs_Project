       // Système de navigation entre les pages
       document.addEventListener('DOMContentLoaded', function() {
        // Gestion des clics sur les liens de navigation
        const navLinks = document.querySelectorAll('.nav-link');
        const pages = document.querySelectorAll('.page');
        
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Retirer la classe active de tous les liens
                navLinks.forEach(navLink => {
                    navLink.classList.remove('active');
                });
                
                // Ajouter la classe active au lien cliqué
                this.classList.add('active');
                
                // Cacher toutes les pages
                pages.forEach(page => {
                    page.classList.remove('active');
                });
                
                // Afficher la page correspondante
                const pageId = this.getAttribute('data-page');
                document.getElementById(pageId).classList.add('active');
            });
        });
        
        // Données des clubs
        const clubs = [
            {
                id: 1,
                name: "Club d'Échecs",
                description: "Rejoignez notre communauté d'amateurs d'échecs et participez à nos tournois.",
                members: 24,
                events: 3,
                category: "Jeux"
            },
            {
                id: 2,
                name: "Club Dev Web",
                description: "Ateliers de programmation, hackathons et conférences sur les nouvelles technologies.",
                members: 45,
                events: 5,
                category: "Technologie"
            },
            {
                id: 3,
                name: "Club Robotique",
                description: "Conception et programmation de robots pour des compétitions inter-universités.",
                members: 32,
                events: 2,
                category: "Technologie"
            },
            {
                id: 4,
                name: "Club Artistique",
                description: "Espace de création et d'expression artistique pour tous les talents.",
                members: 18,
                events: 4,
                category: "Art"
            },
            {
                id: 5,
                name: "Club Entrepreneuriat",
                description: "Développez vos projets entrepreneuriaux avec le soutien de mentors.",
                members: 28,
                events: 3,
                category: "Business"
            },
            {
                id: 6,
                name: "Club Débat",
                description: "Améliorez vos compétences en communication et participez à des débats.",
                members: 15,
                events: 2,
                category: "Communication"
            }
        ];
        
        // Données des événements
        const events = [
            {
                id: 1,
                title: "Tournoi d'échecs",
                date: "12 Octobre 2023 - 14h",
                location: "Salle B12, Bâtiment Principal",
                participants: 15,
                club: "Club d'Échecs"
            },
            {
                id: 2,
                title: "Hackathon 2023",
                date: "15-16 Octobre 2023",
                location: "Labo Informatique, Bâtiment B",
                participants: 8,
                club: "Club Dev Web"
            },
            {
                id: 3,
                title: "Atelier Robotique",
                date: "20 Octobre 2023 - 16h",
                location: "Atelier Tech, Bâtiment C",
                participants: 12,
                club: "Club Robotique"
            },
            {
                id: 4,
                title: "Exposition Artistique",
                date: "25 Octobre 2023 - 18h",
                location: "Galerie d'Art, Bâtiment A",
                participants: 0,
                club: "Club Artistique"
            }
        ];
        
        // Afficher tous les clubs
        function displayAllClubs() {
            const clubsGrid = document.querySelector('#clubs .cards-grid');
            clubsGrid.innerHTML = '';
            
            clubs.forEach(club => {
                clubsGrid.innerHTML += `
                    <div class="card">
                        <img src="https://source.unsplash.com/random/300x200/?${club.category}" alt="${club.name}" class="card-img">
                        <div class="card-body">
                            <h3 class="card-title">${club.name}</h3>
                            <p class="card-text">${club.description}</p>
                            <div class="card-meta">
                                <span><i class="fas fa-users"></i> ${club.members} membres</span>
                                <span><i class="fas fa-calendar"></i> ${club.events} événements</span>
                            </div>
                            <a href="#" class="btn"><i class="fas fa-eye"></i> Voir détails</a>
                            <a href="#" class="btn btn-outline" style="margin-left: 0.5rem;"><i class="fas fa-plus"></i> Rejoindre</a>
                        </div>
                    </div>
                `;
            });
        }
        
        // Afficher tous les événements
        function displayAllEvents() {
            const eventsGrid = document.querySelector('#events .cards-grid');
            eventsGrid.innerHTML = '';
            
            events.forEach(event => {
                eventsGrid.innerHTML += `
                    <div class="card">
                        <img src="https://source.unsplash.com/random/300x200/?${event.club.split(' ')[1]}" alt="${event.title}" class="card-img">
                        <div class="card-body">
                            <h3 class="card-title">${event.title}</h3>
                            <p class="card-text"><i class="far fa-calendar-alt"></i> ${event.date}</p>
                            <p class="card-text"><i class="fas fa-map-marker-alt"></i> ${event.location}</p>
                            <p class="card-text"><i class="fas fa-users"></i> Organisé par ${event.club}</p>
                            <div class="card-meta">
                                <span><i class="fas fa-users"></i> ${event.participants} participants</span>
                            </div>
                            <a href="#" class="btn btn-accent"><i class="fas fa-check"></i> S'inscrire</a>
                        </div>
                    </div>
                `;
            });
        }
        
        // Initialiser l'affichage
        displayAllClubs();
        displayAllEvents();
        
        // Gestion des clics sur les cartes
        document.addEventListener('click', function(e) {
            if (e.target.closest('.card') && !e.target.closest('.btn')) {
                const card = e.target.closest('.card');
                const title = card.querySelector('.card-title').textContent;
                alert(`Détails du ${title} - Cette fonctionnalité sera implémentée avec une page de détails complète.`);
            }
        });
    });