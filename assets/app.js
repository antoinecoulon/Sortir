import './bootstrap.js';
import './dark-mode.js';
import './styles/app.css';
import 'flowbite';

document.getElementById("address").addEventListener("input", autocompleteAdresse, false);


function autocompleteAdresse(evt) {
    const searchInput = evt.target.value
    const listTarget = document.getElementById('addressList')
    if(searchInput.length > 3) {
        const encodedQuery = encodeURIComponent(searchInput)
        const url = `https://api-adresse.data.gouv.fr/search/?q=${encodedQuery}`
        fetch(url).then(response => {
           return response.json()
        }).then(data => {
           listTarget.innerHTML = renderList(data.features)
        })
    }
}

function renderList(addresses) {
    return `
    <ul>
        ${addresses.map((address) => {
        return `<li>${address.properties.label}</li>`;
    }).join('')}
    </ul>
    `;
}