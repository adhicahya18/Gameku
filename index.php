<?php
// Fungsi untuk membuat atau membuka koneksi ke database SQLite
function openDatabase() {
    $db = new SQLite3('database.db');
    return $db;
}

// Fungsi untuk membuat tabel jika belum ada
function createTable() {
    $db = openDatabase();
    $query = "CREATE TABLE IF NOT EXISTS exam_links (
        id INTEGER PRIMARY KEY,
        subject TEXT,
        link TEXT,
        token TEXT,
        use_token INTEGER DEFAULT 0
    )"; // Tambahkan kolom use_token
    $db->exec($query);
    $db->close();
}

// Fungsi untuk menyimpan link ujian ke dalam database
function saveExamLink($subject, $link, $token, $useToken) {
    $db = openDatabase();
    $subject = $db->escapeString($subject);
    $link = $db->escapeString($link);
    $token = $db->escapeString($token);
    $query = "INSERT INTO exam_links (subject, link, token, use_token) VALUES ('$subject', '$link', '$token', '$useToken')";
    $db->exec($query);
    $db->close();
}

// Fungsi untuk mengambil semua link ujian dari database
function getAllExamLinks() {
    $db = openDatabase();
    $result = $db->query("SELECT * FROM exam_links");
    $links = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $links[] = $row;
    }
    $db->close();
    return $links;
}

// Fungsi untuk mengedit link ujian di database
function editExamLink($id, $subject, $link, $token, $useToken) {
    $db = openDatabase();
    $subject = $db->escapeString($subject);
    $link = $db->escapeString($link);
    $token = $db->escapeString($token);
    $query = "UPDATE exam_links SET subject='$subject', link='$link', token='$token', use_token='$useToken' WHERE id=$id";
    $db->exec($query);
    $db->close();
}

// Fungsi untuk menghapus link ujian dari database
function deleteExamLink($id) {
    $db = openDatabase();
    $query = "DELETE FROM exam_links WHERE id=$id";
    $db->exec($query);
    $db->close();
}

// Membuat tabel jika belum ada
createTable();

// Menangani penambahan link ujian
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if(isset($_POST['action']) && $_POST['action'] == 'add_link'){
        $subject = $_POST['subject'];
        $link = $_POST['link'];
        $token = $_POST['token'];
        $useToken = isset($_POST['use_token']) ? 1 : 0; // Memeriksa apakah opsi token dipilih
        saveExamLink($subject, $link, $token, $useToken);
        header('Location: index.php');
    }

    // Menangani edit link ujian
    if(isset($_POST['action']) && $_POST['action'] == 'edit_link'){
        $id = $_POST['id'];
        $subject = $_POST['subject'];
        $link = $_POST['link'];
        $token = $_POST['token'];
        $useToken = isset($_POST['use_token']) ? 1 : 0; // Memeriksa apakah opsi token dipilih
        editExamLink($id, $subject, $link, $token, $useToken);
        header('Location: index.php');
    }

    // Menangani hapus link ujian
    if(isset($_POST['action']) && $_POST['action'] == 'delete_link'){
        $id = $_POST['id'];
        deleteExamLink($id);
        header('Location: index.php');
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>E-learning Mathematic</title>
    <style>
        /* CSS untuk notifikasi */
        .notification {
            position: fixed;
            bottom: 20px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0, 0, 0, 0.8);
            color: #fff;
            padding: 10px 20px;
            border-radius: 5px;
            z-index: 9999;
            transition: opacity 0.5s ease;
        }

        .notification-hidden {
            opacity: 0;
        }

        .notification-visible {
            opacity: 1;
        }

        /* Desain Boxbee 3D */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(45deg, #00b4db, #0083b0); /* Gradien latar belakang */
            text-align: center;
        }

        .logo {
            margin: 20px auto; /* Memusatkan logo */
        }

        h1, h2, h3 {
            color: #fff;
            text-align: center;
        }

        ul {
            list-style-type: none;
            padding: 0;
            margin: 0 auto; /* Memusatkan daftar */
            max-width: 300px; /* Mengatur lebar maksimum */
        }

        li {
            margin-bottom: 10px;
        }

        a {
            text-decoration: none;
            color: #fff;
            display: block;
            padding: 10px;
            background-color: #0b486b; /* Warna latar belakang */
            border-radius: 8px;
            transition: background-color 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Efek bayangan */
            transform: translateZ(0);
            position: relative;
            overflow: hidden;
        }

        a:hover {
            background-color: #15317e; /* Warna latar belakang saat hover */
        }

        .menu {
            background-color: #fff;
            padding: 20px;
            margin: 20px auto; /* Memusatkan menu */
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1); /* Efek bayangan */
            text-align: left;
            max-width: 300px; /* Mengatur lebar maksimum */
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"], input[type="password"], input[type="submit"] {
            padding: 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            margin-right: 10px;
            width: calc(100% - 20px); /* Lebar fleksibel dengan padding */
            box-sizing: border-box; /* Memastikan padding tidak mempengaruhi lebar total */
        }

        input[type="submit"] {
            background-color: #0b486b; /* Warna tombol */
            color: #fff;
            cursor: pointer;
        }

        input[type="submit"]:hover {
            background-color: #15317e; /* Warna tombol saat hover */
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="logo">
        <img src="logo.png" alt="Logo" width="300" height="70"> <!-- Ubah "logo.png" sesuai dengan nama file logo Anda -->
    </div>
    <h1>UJIAN ONLINE</h1>
    <ul>
        <li><a href="javascript:void(0)" onclick="toggleMenu('admin')">Admin Ujian</a></li>
        <li><a href="javascript:void(0)" onclick="toggleMenu('user')">Siswa</a></li>
    </ul>

    <hr>

    <div id="admin" class="menu" style="display:none;">
        <h2>Admin Menu</h2>
        <form id="adminLogin">
            <input type="text" id="adminUsername" placeholder="Username">
            <input type="password" id="adminPassword" placeholder="Password">
            <input type="submit" value="Login">
        </form>
        <ul id="adminMenu" style="display:none;">
            <li><a href="#" onclick="toggleAddLinkForm()">Tambah Link Ujian</a></li>
            <li><a href="https://www.mabhak32.co-1.cloud">Input Nilai</a></li>
            <?php
            $links = getAllExamLinks();
            foreach ($links as $link) {
                echo "<li><a href=\"#\" onclick=\"manageExamLinks('edit', '{$link['id']}', '{$link['subject']}', '{$link['link']}', '{$link['token']}', '{$link['use_token']}')\">Edit Link Ujian: {$link['subject']}</a> (Token: {$link['token']})</li>";
                echo "<li><a href=\"#\" onclick=\"manageExamLinks('delete', '{$link['id']}')\">Hapus Link Ujian: {$link['subject']}</a></li>";
            }
            ?>
        </ul>
        <form id="addLinkForm" style="display:none;" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
            <input type="hidden" name="action" value="add_link">
            <input type="text" name="subject" placeholder="Nama Mata Pelajaran" required><br>
            <input type="text" name="link" placeholder="Link Google Form" required><br>
            <input type="text" name="token" placeholder="Token" required><br>
            <label><input type="checkbox" name="use_token" value="1"> Gunakan Token</label><br> <!-- Checkbox untuk memilih apakah menggunakan token atau tidak -->
            <input type="submit" value="Simpan">
        </form>
    </div>

    <div id="user" class="menu" style="display:none;">
        <h2>Siswa Menu</h2>
        <ul>
            <li><a href="#" onclick="showExamLinks()">Ujian</a></li>
            <!-- Tambahkan div untuk menampilkan daftar link ujian -->
            <div id="exam-links"></div>
            <li><a href="#exam-results">Nilai Ujian</a></li>
            <li><a href="https://mabhak32.co-1.cloud">Kembali Ke Menu Utama</a></li>
        </ul>
    </div>

    <!-- Notifikasi -->
    <div id="customNotification" class="notification notification-hidden">
        <p id="notificationMessage"></p>
    </div>

    <script>
        var subjectLinks = {}; // Objek untuk menyimpan link Google Form berdasarkan mata pelajaran
        var isAdminLoggedIn = false;

        function toggleMenu(menuId) {
            if (!isAdminLoggedIn && menuId === 'admin')

            var menu = document.getElementById(menuId);
            var menus = document.querySelectorAll('.menu');
            menus.forEach(function(item) {
                if (item.id === menuId) {
                    item.style.display = 'block';
                    if (menuId === 'admin' && isAdminLoggedIn) {
                        document.getElementById('adminMenu').style.display = 'block';
                    }
                } else {
                    item.style.display = 'none';
                }
            });
        }

        // Fungsi untuk menampilkan link ujian Google Form berdasarkan mata pelajaran
        function showExamLinks() {
            var examLinksContainer = document.getElementById('exam-links');
            examLinksContainer.innerHTML = ''; // Clear existing links

            <?php
            $links = getAllExamLinks();
            foreach ($links as $link) {
                if ($link['use_token'] == 1) { // Tambahkan kondisi untuk menampilkan link dengan atau tanpa token
                    echo "var linkElement = document.createElement('a');";
                    echo "linkElement.href = '#';";
                    echo "linkElement.textContent = '{$link['subject']}';";
                    echo "linkElement.onclick = function() { verifyToken('{$link['link']}', '{$link['token']}') };";
                    echo "var listItem = document.createElement('li');";
                    echo "listItem.appendChild(linkElement);";
                    echo "examLinksContainer.appendChild(listItem);";
                } else {
                    echo "var linkElement = document.createElement('a');";
                    echo "linkElement.href = '{$link['link']}';"; // Langsung arahkan ke link tanpa verifikasi token
                    echo "linkElement.textContent = '{$link['subject']}';";
                    echo "var listItem = document.createElement('li');";
                    echo "listItem.appendChild(linkElement);";
                    echo "examLinksContainer.appendChild(listItem);";
                }
            }
            ?>

            // Tampilkan pesan jika tidak ada materi atau ujian yang tersedia
            if (<?php echo count($links); ?> === 0) {
                var messageElement = document.createElement('p');
                messageElement.textContent = "Belum ada materi atau ujian yang tersedia.";
                examLinksContainer.appendChild(messageElement);
            }
        }

        // Fungsi untuk menampilkan notifikasi
        function showNotification(message) {
            var notification = document.getElementById('customNotification');
            var notificationMessage = document.getElementById('notificationMessage');
            notificationMessage.textContent = message;
            notification.classList.remove('notification-hidden');
            notification.classList.add('notification-visible');
            setTimeout(function() {
                notification.classList.remove('notification-visible');
                notification.classList.add('notification-hidden');
            }, 3000); // Hide the notification after 3 seconds
        }

        // Fungsi untuk toggle form penambahan link ujian
        function toggleAddLinkForm() {
            var form = document.getElementById('addLinkForm');
            if (form.style.display === 'none') {
                form.style.display = 'block';
            } else {
                form.style.display = 'none';
            }
        }

        // Fungsi untuk menangani operasi pada link ujian (edit, delete)
        function manageExamLinks(action, id, subject, link, token, useToken) {
            if (action === 'edit') {
                var newSubject = prompt("Masukkan nama mata pelajaran baru:", subject);
                var newLink = prompt("Masukkan link Google Form baru untuk " + newSubject + ":", link);
                var newToken = prompt("Masukkan token baru untuk " + newSubject + ":", token);
                var useToken = confirm("Gunakan token untuk mata pelajaran " + newSubject + "?"); // Prompt untuk memilih apakah menggunakan token
                if (newSubject && newLink && ((useToken && newToken) || !useToken)) {
                    subjectLinks[newSubject] = newLink;
                    delete subjectLinks[subject];
                    showNotification("Link Google Form untuk " + newSubject + " berhasil diperbarui!");
                    var form = document.createElement('form');
                    form.method = 'post';
                    form.action = '<?php echo $_SERVER['PHP_SELF']; ?>';
                    var inputAction = document.createElement('input');
                    inputAction.type = 'hidden';
                    inputAction.name = 'action';
                    inputAction.value = 'edit_link';
                    form.appendChild(inputAction);
                    var inputId = document.createElement('input');
                    inputId.type = 'hidden';
                    inputId.name = 'id';
                    inputId.value = id;
                    form.appendChild(inputId);
                    var inputSubject = document.createElement('input');
                    inputSubject.type = 'hidden';
                    inputSubject.name = 'subject';
                    inputSubject.value = newSubject;
                    form.appendChild(inputSubject);
                    var inputLink = document.createElement('input');
                    inputLink.type = 'hidden';
                    inputLink.name = 'link';
                    inputLink.value = newLink;
                    form.appendChild(inputLink);
                    var inputToken = document.createElement('input');
                    inputToken.type = 'hidden';
                    inputToken.name = 'token';
                    inputToken.value = newToken;
                    form.appendChild(inputToken);
                    var inputUseToken = document.createElement('input');
                    inputUseToken.type = 'hidden';
                    inputUseToken.name = 'use_token';
                    inputUseToken.value = useToken ? 1 : 0;
                    form.appendChild(inputUseToken);
                    document.body.appendChild(form);
                    form.submit();
                }
            } else if (action === 'delete') {
                var confirmation = confirm("Apakah Anda yakin ingin menghapus link Google Form untuk " + subject + "?");
                if (confirmation) {
                    delete subjectLinks[subject];
                    showNotification("Link Google Form untuk " + subject + " telah dihapus!");
                    var form = document.createElement('form');
                    form.method = 'post';
                    form.action = '<?php echo $_SERVER['PHP_SELF']; ?>';
                    var inputAction = document.createElement('input');
                    inputAction.type = 'hidden';
                    inputAction.name = 'action';
                    inputAction.value = 'delete_link';
                    form.appendChild(inputAction);
                    var inputId = document.createElement('input');
                    inputId.type = 'hidden';
                    inputId.name = 'id';
                    inputId.value = id;
                    form.appendChild(inputId);
                    document.body.appendChild(form);
                    form.submit();
                }
            }
        }

        // Fungsi untuk memverifikasi token sebelum mengakses link ujian
        function verifyToken(link, token) {
            var enteredToken = prompt("Masukkan token:");
            if (enteredToken === token) {
                window.location.href = link;
            } else {
                alert('Token tidak valid.');
            }
        }

        // Fungsi untuk melakukan login admin
        function adminLogin(event) {
            event.preventDefault();
            var username = document.getElementById('adminUsername').value;
            var password = document.getElementById('adminPassword').value;
            // Proses login disini
            // Contoh sederhana: jika username dan password sesuai, set isAdminLoggedIn menjadi true
            if (username === 'admin' && password === 'password') {
                isAdminLoggedIn = true;
                document.getElementById('adminLogin').style.display = 'none';
                document.getElementById('adminMenu').style.display = 'block';
            } else {
                alert("Username atau password salah.");
            }
        }

        document.getElementById('adminLogin').addEventListener('submit', adminLogin);
    </script>
</body>
</html>


