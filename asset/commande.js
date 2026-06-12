const selectMenu = document.getElementById('id_menu');
const inputNb = document.getElementById('nb_personnes');
const inputVille = document.getElementById('ville');
const inputDistance = document.getElementById('distance_km');
const blocDistance = document.getElementById('bloc-distance');
const aideNb = document.getElementById('aide-nb-personnes');

const recapPrixMenu = document.getElementById('recap-prix-menu');
const recapRemise = document.getElementById('recap-remise');
const recapLivraison = document.getElementById('recap-livraison');
const recapTotal = document.getElementById('recap-total');

function formatEuro(montant) {
    return montant.toFixed(2).replace('.', ',') + ' €';
}

function getMenuData() {
    const option = selectMenu.options[selectMenu.selectedIndex];
    if (!option || !option.value) return null;
    return {
        prixMin: parseFloat(option.dataset.prixMin),
        nbMin: parseInt(option.dataset.nbMin, 10),
    };
}

function calculerLivraison(ville, distanceKm) {
    if (ville.trim().toLowerCase() === 'bordeaux') {
        return 0;
    }
    return 5 + (distanceKm * 0.59);
}

function mettreAJourRecap() {
    const menu = getMenuData();
    const nbPersonnes = parseInt(inputNb.value, 10) || 0;

    if (!menu || nbPersonnes <= 0) {
        recapPrixMenu.textContent = '0,00 €';
        recapRemise.textContent = '0,00 €';
        recapLivraison.textContent = '0,00 €';
        recapTotal.textContent = '0,00 €';
        return;
    }

    aideNb.textContent = 'Minimum : ' + menu.nbMin + ' personnes';
    inputNb.min = menu.nbMin;

    const prixBase = menu.prixMin * (nbPersonnes / menu.nbMin);
    let remise = 0;
    if (nbPersonnes >= menu.nbMin + 5) {
        remise = prixBase * 0.10;
    }
    const prixMenu = prixBase - remise;

    const horsBordeaux = inputVille.value.trim().toLowerCase() !== 'bordeaux';
    blocDistance.style.display = horsBordeaux ? 'block' : 'none';
    if (!horsBordeaux) {
        inputDistance.value = '0';
    }

    const distanceKm = parseFloat(inputDistance.value) || 0;
    const prixLivraison = calculerLivraison(inputVille.value, distanceKm);
    const total = prixMenu + prixLivraison;

    recapPrixMenu.textContent = formatEuro(prixMenu);
    recapRemise.textContent = formatEuro(remise);
    recapLivraison.textContent = formatEuro(prixLivraison);
    recapTotal.textContent = formatEuro(total);
}

[selectMenu, inputNb, inputVille, inputDistance].forEach((el) => {
    el.addEventListener('input', mettreAJourRecap);
    el.addEventListener('change', mettreAJourRecap);
});

mettreAJourRecap();