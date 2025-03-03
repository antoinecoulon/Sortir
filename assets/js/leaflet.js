import "leaflet"
import 'leaflet/dist/leaflet.css';

document.addEventListener('DOMContentLoaded', () => {
    const icon = L.icon({
        iconUrl: markerLocation,
        iconSize: [28, 32],
        popupAnchor: [-3, -15]
    })

    let map = L.map('map').setView([latitude, longitude], 13);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
    }).addTo(map);

    let marker = L.marker([latitude, longitude], {icon: icon}).addTo(map);

    marker.bindPopup(locationName).openPopup();
});