// app.js - Main application functionality

// DOM Elements
const recentClubList = document.getElementById('recentClubList');
const upcomingEventsList = document.getElementById('upcomingEventsList');
const logoutBtn = document.getElementById('logout-btn');
const loadingOverlay = document.getElementById('loadingOverlay');

// API URL (would be configured to your backend server in production)
const API_BASE_URL = 'https://api.example.com';

// Mock Data (Replace with actual API calls in production)
const clubs = [
    {
        id: 1,
        name: 'Club Robotique',
        category: 'technology',
        categoryLabel: 'Technologie',
        members: 28,
        president: 'Sophie Martin',
        logo: '/api/placeholder/50/50',
        createdAt: '2025-01-15'
    },
    {
        id: 2,
        name: 'Club Débat',
        category: 'culture',
        categoryLabel: 'Culture',
        members: 42,
        president: 'Thomas Bernard',
        logo: '/api/placeholder/50/50',
        createdAt: '2025-02-03'
    },
    {
        id: 3,
        name: 'Club Art Numérique',
        category: 'arts',
        categoryLabel: 'Arts',
        members: 16,
        president: 'Emma Dubois',
        logo: '/api/placeholder/50/50',
        createdAt: '2025-03-12'
    },
    {
        id: 4,
        name: 'Club E-sport',
        category: 'technology',
        categoryLabel: 'Technologie',
        members: 35,
        president: 'Lucas Moreau',
        logo: '/api/placeholder/50/50',
        createdAt: '2025-01-28'
    }
];

const events = [
    {
        id: 1,
        title: 'Atelier Robotique',
        club: 'Club Robotique',
        date: '2025-04-20',
        time: '14:00',
        location: 'Salle B204',
        participants: 15
    },
    {
        id: 2,
        title: 'Débat: Intelligence Artificielle',
        club: 'Club Débat',
        date: '2025-04-25',
        time: '16:30',
        location: 'Amphithéâtre A',
        participants: 47
    },
    {
        id: 3,
        title: 'Exposition Art Numérique',
        club: 'Club Art Numérique',
        date: '2025-05-03',
        time: '10:00',
        location: 'Hall Principal',
        participants: 23
    },
    {
        id: 4,
        title: 'Tournoi League of Legends',
        club: 'Club E-sport',
        date: '2025-04-28',
        time: '18:00',
        location: 'Salle Informatique C'
    }
];