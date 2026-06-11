const formFiltres = document.getElementById('filtres');
const listeMenus = document.getElementById('liste-menus');

async function chargerMenus() {
    const params = new URLSearchParams(new FormData(formFiltres));
    const reponse = await fetch('api/get-menus.php?' + params.toString());
    const menus = await reponse.json();

    listeMenus.innerHTML = '';

    if (menus.length === 0) {
        listeMenus.innerHTML = '<p class="text-muted">Aucun menu ne correspond à vos critères.</p>';
        return;
    }

    for (const menu of menus) {
        const colonne = document.createElement('div');
        colonne.className = 'col-md-4';

        const carte = document.createElement('div');
        carte.className = 'card h-100';

        const corps = document.createElement('div');
        corps.className = 'card-body d-flex flex-column';

        const titre = document.createElement('h2');
        titre.className = 'card-title h5';
        titre.textContent = menu.titre;

        const description = document.createElement('p');
        description.className = 'card-text';
        description.textContent = menu.description;

        const infos = document.createElement('p');
        infos.className = 'fw-bold';
        infos.textContent = `À partir de ${menu.nb_personnes_min} personnes — ${menu.prix_min} €`;

        const lien = document.createElement('a');
        lien.className = 'btn btn-primary mt-auto';
        lien.href = 'menu-detail.php?id=' + menu.id_menu;
        lien.textContent = 'Voir le détail';

        corps.append(titre, description, infos, lien);
        carte.append(corps);
        colonne.append(carte);
        listeMenus.append(colonne);
    }
}

// Rechargement dynamique à chaque modification d'un filtre, sans rechargement de page
formFiltres.addEventListener('input', chargerMenus);

// Premier affichage au chargement de la page
chargerMenus();