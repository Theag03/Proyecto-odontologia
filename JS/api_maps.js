function initMap() {
    // Coordenadas exactas de tu clínica (ejemplo para Caracas)
    const clinicLocation = { lat: 10.5000, lng: -66.9167 };
    
    const map = new google.maps.Map(document.getElementById("googleMap"), {
        zoom: 16,
        center: clinicLocation,
        mapId: "YOUR_MAP_ID", // Opcional: para estilos personalizados
        disableDefaultUI: false // Personaliza controles
    });

    // Marcador personalizado
    new google.maps.Marker({
        position: clinicLocation,
        map,
        title: "AgendaDental",
        icon: {
            url: "https://maps.google.com/mapfiles/ms/icons/red-dot.png",
            scaledSize: new google.maps.Size(40, 40)
        }
    });
}

