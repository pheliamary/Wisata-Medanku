/**
 * assets/js/map.js v2.0
 * Leaflet — SIG Wisata Medan
 */

const KAT_COLOR = {
  sejarah:'#3b82f6', religi:'#8b5cf6', edukasi:'#ef4444',
  alam:'#2d9b57', kuliner:'#f59e0b', olahraga:'#ec4899', default:'#6b7280'
};

function markerIcon(kategori, size=32) {
  const c = KAT_COLOR[kategori] || KAT_COLOR.default;
  const s = size, h = Math.round(size*1.3);
  return L.divIcon({
    html:`<svg xmlns="http://www.w3.org/2000/svg" width="${s}" height="${h}" viewBox="0 0 32 42">
      <filter id="sh"><feDropShadow dx="0" dy="2" stdDeviation="2" flood-color="rgba(0,0,0,.3)"/></filter>
      <path d="M16 0C7.16 0 0 7.16 0 16c0 10 16 26 16 26S32 26 32 16C32 7.16 24.84 0 16 0z" fill="${c}" filter="url(#sh)"/>
      <circle cx="16" cy="16" r="8" fill="white" opacity=".92"/>
      <circle cx="16" cy="16" r="4" fill="${c}"/>
    </svg>`,
    className:'', iconSize:[s,h], iconAnchor:[s/2,h], popupAnchor:[0,-h]
  });
}

// Global map instance + marker registry
window.SIG = window.SIG || {};

document.addEventListener('DOMContentLoaded', () => {
  const mapEl = document.getElementById('map');
  if (!mapEl) return;

  const map = L.map('map', { center:[3.5952,98.6722], zoom:13, zoomControl:false });
  window.SIG.map = map;
  window.SIG.markers = {};

  L.control.zoom({ position:'bottomright' }).addTo(map);

  const carto = L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',{
    attribution:'© <a href="https://carto.com">CARTO</a>', maxZoom:19
  }).addTo(map);

  const osm = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png',{
    attribution:'© OpenStreetMap', maxZoom:19
  });

  const satellite = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}',{
    attribution:'© Esri', maxZoom:19
  });



  fetch('controllers/api_wisata.php?action=map')
    .then(r=>r.json())
    .then(geo=>{
      const groups={};
      const overlays={};

      geo.features.forEach(f=>{
        const [lng,lat] = f.geometry.coordinates;
        const p = f.properties;
        const kat = p.kategori||'default';
        const col = KAT_COLOR[kat]||KAT_COLOR.default;

        if(!groups[kat]){
          groups[kat] = L.layerGroup().addTo(map);
          overlays[`<span style="display:inline-flex;align-items:center;gap:6px"><span style="width:10px;height:10px;border-radius:50%;background:${col};display:inline-block"></span>${kat.charAt(0).toUpperCase()+kat.slice(1)}</span>`] = groups[kat];
        }

        const tiket = p.tiket>0 ? 'Rp '+parseInt(p.tiket).toLocaleString('id-ID') : '<span style="color:#2d9b57;font-weight:700">Gratis</span>';
        const popup = `<div class="popup-inner">
          <div class="popup-name">${p.nama}</div>
          <div class="popup-sub">📍 ${p.kelurahan||''}, ${p.kecamatan||''}</div>
          <div style="display:flex;gap:.5rem;font-size:.75rem;margin-bottom:.65rem">
            <span>🕐 ${p.jam||'-'}</span><span>🎫 ${tiket}</span>
          </div>
          <button class="popup-btn" onclick="handleCardClick(${p.id}, ${lat}, ${lng})">Lihat Detail →</button>
        </div>`;

        const marker = L.marker([lat,lng],{icon:markerIcon(kat)})
          .bindPopup(popup,{maxWidth:240,closeButton:false});

        groups[kat].addLayer(marker);
        window.SIG.markers[p.id] = {marker, lat, lng};
      });

      // Re-add layer control with category overlays
      L.control.layers({'Peta Ringan':carto,'OpenStreetMap':osm,'Satelit':satellite}, overlays, {collapsed:false}).addTo(map);
    })
    .catch(e=>console.error('Map load error:',e));
});

/** Zoom peta ke wisata tertentu */
window.zoomToWisata = function(id, lat, lng) {
  const mapSection = document.getElementById('peta');
  if(mapSection) mapSection.scrollIntoView({behavior:'smooth', block:'start'});
  setTimeout(()=>{
    const map = window.SIG.map;
    if(!map) return;
    map.flyTo([lat,lng], 17, {animate:true, duration:1.2});
    const m = window.SIG.markers[id];
    if(m) setTimeout(()=>m.marker.openPopup(), 1300);
  }, 600);
};

/** Map Picker untuk admin form */
window.initMapPicker = function(lat, lng, inputLat, inputLng) {
  const il = parseFloat(lat)||3.5952, ilng = parseFloat(lng)||98.6722;
  const pm = L.map('map-picker').setView([il,ilng],15);
  L.tileLayer('https://{s}.basemaps.cartocdn.com/light_all/{z}/{x}/{y}{r}.png',{maxZoom:19}).addTo(pm);
  let mk = L.marker([il,ilng],{draggable:true}).addTo(pm);
  const upd = ll => {
    document.getElementById(inputLat).value = ll.lat.toFixed(7);
    document.getElementById(inputLng).value = ll.lng.toFixed(7);
  };
  upd(mk.getLatLng());
  mk.on('dragend', e=>upd(e.target.getLatLng()));
  pm.on('click', e=>{ mk.setLatLng(e.latlng); upd(e.latlng); });
  
  window.SIG = window.SIG || {};
  window.SIG.mapPickerMap = pm;
  window.SIG.mapPickerMarker = mk;
};
