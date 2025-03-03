const searchInput = document.getElementById('address')
const listTarget = document.getElementById('addressList')
const createAddress = document.getElementById('createAddress')
const locationNameInput = document.getElementById('location-name-input')
const addressForm = document.getElementById('addressForm')
const modal = document.querySelector('[data-modal-toggle="location-modal"]');
const selectLocation = document.getElementById("event_location")

let locationNameValue = ""
let addresses = []
let address = null

// EVENTS
searchInput.addEventListener("input", autocompleteAdresse);
createAddress.addEventListener("click", sendForm);
locationNameInput.addEventListener("input", (evt => locationNameValue = evt.target.value))

addressForm.addEventListener("submit", evt => evt.preventDefault())


function autocompleteAdresse(evt) {
    const searchValue = evt.target.value
    if (searchValue.length > 3) {
        const encodedQuery = encodeURIComponent(searchValue)
        const url = `https://api-adresse.data.gouv.fr/search/?q=${encodedQuery}`
        fetch(url).then(response => {
            return response.json()
        }).then(data => {
            addresses = data.features
            listTarget.innerHTML = renderList()
            addClickListener(addresses.length)
        })
    }
}

function renderList() {
    if (addresses.length > 0) {
        return `
    <ul class="p-4 flex flex-col gap-2">
        ${addresses.map((address, idx) => {
            return `<li class="cursor-pointer" id="address-${idx}">${address.properties.label}</li>`;

        }).join('')}
    </ul>`
    } else {
        return ``
    }
}

function addClickListener(length) {
    let idx = 0;
    while (idx < length) {
        document.getElementById(`address-${idx}`).addEventListener("click", evt => {
            const addressIdx = evt.target.id.split("-")[1]
            address = addresses[addressIdx].properties
            searchInput.value = address.label
            // DEBUG document.getElementById("data-address").innerHTML = `<pre>${JSON.stringify(address, null, 2)}</pre>`
            addresses = []
            listTarget.innerHTML = renderList()
        })
        idx++;
    }
}


async function sendForm() {
    if (address && locationNameValue && addressForm.checkValidity()) {
        const location = {
            name: locationNameValue,
            street: address.street,
            streetNumber: address.housenumber ?? null,
            city: address.city,
            cp: address.postcode,
            latitude: address.y,
            longitude: address.x
        }

        const body = {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(location),
        }
        try {
            const response = await fetch('/location/create', body)
            const data = await response.json()
            let newLocationOption = document.createElement('option');
            newLocationOption.value = data?.locationId;
            newLocationOption.textContent = data?.locationName;
            selectLocation.appendChild(newLocationOption)
            selectLocation.value = newLocationOption.value
            modal.click() // close modal
        } catch (error) {
            console.error(error)
        }
    }
}

