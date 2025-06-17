const fs = require('fs');
const path = require('path');

const usersData = JSON.parse(fs.readFileSync('storage/users.json', 'utf8')); // Mauvais : lu une seule fois

function login(username, password) {
    const user = usersData.users.find(u => u.username === username);
    // ...vérification du mot de passe...
    // ...retourne l'utilisateur ou une erreur...
}