<?php
// Koneksi ke Database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "pgweb8";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data dari tabel database
$sql = "SELECT * FROM `sebaran faskes`";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Metadata -->
    <meta charset="utf-8">
    <title>Web GIS & Tabel Sebaran Faskes</title>

    <!-- Leaflet CSS Library -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css">

    <style>
        /* Pengaturan Halaman */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f8f9fa;
            color: #2d3748;
        }

        /* Judul di Bagian Atas */
        .page-header {
            background-color: #ffffff;
            padding: 16px 20px;
            border-bottom: 1px solid #e2e8f0;
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.03);
        }

        .page-header h2 {
            margin: 0;
            font-size: 22px;
            font-weight: 600;
            color: #2d3748;
        }

        /* Container Utama Atas-Bawah */
        .content-container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 15px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        /* Kotak Peta di Atas */
        #map {
            height: 450px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #cbd5e0;
        }

        /* Kotak Tabel di Bawah */
        .table-container {
            background: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
            border: 1px solid #cbd5e0;
            overflow-x: auto;
        }

        /* Tombol Tambah Data */
        .btn-input {
            display: inline-block;
            padding: 7px 12px;
            background-color: #38a169;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-bottom: 15px;
            font-size: 12px;
            font-weight: 600;
            transition: background-color 0.2s;
        }

        .btn-input:hover {
            background-color: #2f855a;
        }

        /* Styling Tabel */
        table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
            font-size: 12px;
        }

        th, td {
            padding: 10px 12px;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }

        th {
            background-color: #3182ce;
            color: white;
            font-weight: 600;
            text-transform: uppercase;
            font-size: 11px;
            letter-spacing: 0.5px;
        }

        tr:hover {
            background-color: #f7fafc;
        }

        /* Tombol Aksi (Edit & Hapus) */
        .btn-edit, .btn-delete {
            padding: 4px 8px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 11px;
            font-weight: 600;
            display: inline-block;
            margin-right: 4px;
        }

        .btn-edit {
            background-color: #edf2f7;
            color: #3182ce;
            border: 1px solid #cbd5e0;
        }
        .btn-edit:hover {
            background-color: #e2e8f0;
        }

        .btn-delete {
            background-color: #fff5f5;
            color: #e53e3e;
            border: 1px solid #feb2b2;
        }
        .btn-delete:hover {
            background-color: #fed7d7;
        }
    </style>
</head>
<body>

    <!-- JUDUL -->
    <div class="page-header">
        <h2>Peta Web GIS dan Tabel Sebaran Faskes Mandiri Sleman 2023</h2>
    </div>

    <!-- CONTAINER UTAMA (ATAS: Peta, BAWAH: Tabel) -->
    <div class="content-container">
        
        <!-- PETA DI BAGIAN ATAS -->
        <div id="map"></div>

        <!-- TABEL DI BAGIAN BAWAH -->
        <div class="table-container">
            <a href='input/index.html' class='btn-input'>+ Tambah Data Faskes Baru</a>

            <?php
            if ($result && $result->num_rows > 0) {
                echo "<table><tr> 
                <th style='width: 50px; text-align: center;'>No</th> 
                <th>Nama Faskes</th> 
                <th>Kalurahan</th> 
                <th>Kecamatan</th> 
                <th>Jam Buka</th> 
                <th>Longitude</th> 
                <th>Latitude</th> 
                <th style='text-align: center;'>Aksi</th></tr>"; 
               
                $no = 1;
                while($row = $result->fetch_assoc()) { 
                    echo "<tr>".
                         "<td style='text-align: center;'>".$no++."</td>".
                         "<td><strong>".$row["nama_faskes"]."</strong></td>". 
                         "<td>".$row["kalurahan"]."</td>". 
                         "<td>".$row["kecamatan"]."</td>". 
                         "<td>".$row["jam_buka"]."</td>". 
                         "<td>".$row["longitude"]."</td>". 
                         "<td>".$row["latitude"]."</td>". 
                         "<td style='text-align: center;'>
                            <a href='edit/index.php?id=".$row["id"]."' class='btn-edit'>Edit</a>
                            <a href='delete.php?id=".$row["id"]."' class='btn-delete' onclick='return confirm(\"Yakin ingin menghapus data ini?\")'>Hapus</a>
                          </td>". 
                         "</tr>"; 
                } 
                echo "</table>";
            } else {
                echo "<p style='font-size:12px; color: #718096;'>Belum ada data faskes di dalam database.</p>";
            }
            ?>
        </div>

    </div>

    <!-- Leaflet JavaScript Library -->
    <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js"></script>
    
    <script>
        /* Initial Map */
        var map = L.map('map').setView([-7.77, 110.37], 11); 
             
        /* Tile Basemap */
        var basemap1 = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', { 
            attribution: 'Tiles &copy; Esri' 
        }); 

        var basemap2 = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', { 
            maxZoom: 20, 
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'], 
            attribution: 'Map data &copy; Google' 
        }); 

        basemap1.addTo(map); 

        /* Layer Group untuk menampung marker dari database */
        var faskesLayer = L.layerGroup().addTo(map);

        /* MEMASUKKAN DATA MARKER DARI DATABASE PHP */
        <?php
        $result->data_seek(0);
        if ($result && $result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $lat = $row['latitude'];
                $lon = $row['longitude'];
                $nama = addslashes($row['nama_faskes']);
                $kalurahan = addslashes($row['kalurahan']);
                $kecamatan = addslashes($row['kecamatan']);
                
                echo "var marker = L.marker([$lat, $lon]);\n";
                echo "marker.bindPopup('<b>$nama</b><br>Kalurahan: $kalurahan<br>Kecamatan: $kecamatan');\n";
                echo "marker.addTo(faskesLayer);\n";
            }
        }
        ?>

        /* Control Layer */ 
        var baseMaps = { 
            "Esri Imagery": basemap1, 
            "Google Street": basemap2 
        }; 

        var overlayMaps = { 
            "Sebaran Faskes": faskesLayer
        }; 

        L.control.layers(baseMaps, overlayMaps, {collapsed: false}).addTo(map); 

        /* Scale Bar */ 
        L.control.scale({ 
            maxWidth: 150, position: 'bottomright' 
        }).addTo(map); 
    </script>
</body>
</html>

<?php

$conn->close();
?>