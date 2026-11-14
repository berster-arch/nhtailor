<?php
    include "koneksi.php";
    $data = mysqli_query($koneksi,"select*from galeri order by id desc");
    while($d= mysqli_fetch_array($data)){
        ?>
<html>
    <head>
        <title>Galeri Karya</title>
    </head>
    <body>
       <table>
            <tr>
                <td><h3><?php echo $d['gambar'];?></h3></td>
            </tr>
            <tr>
                <td><?php echo $d['keterangan'];?></td>
            </tr>
        </table>
    </body>
</html>
   <?php }?>