DROP PROCEDURE IF EXISTS p_mon_report_mutasi_stok;

CREATE PROCEDURE p_mon_report_mutasi_stok(IN awal DATE, IN akhir DATE, IN in_jenis INT, IN search VARCHAR(100), IN order_col VARCHAR(100), IN order_type VARCHAR(100), IN q_limit INT, IN q_length INT, IN count_stats INT)

BEGIN
-- jenis 
-- 1. sisa stok
-- 3. tingkat laku produk
-- 4. pergerakan stok
-- 5. usia stok	
-- 6. daftar harga

DECLARE sql_query TEXT;
DECLARE sql_final VARCHAR(100);
DECLARE query_limit VARCHAR(100);

IF(count_stats = 1) THEN
	SET sql_final = "COUNT(*) AS jumlah_record";
	SET query_limit = " ";
ELSE
	SET sql_final = "*";
	SET query_limit = CONCAT("LIMIT ",q_limit,", ",q_length,""); 
END IF;

IF ( in_jenis = 1 ) THEN
SET sql_query = CONCAT("SELECT ",sql_final," FROM (
		SELECT 
			sisa_stok.*
		FROM
			(SELECT v_t_result_table.kd_barang, m_barang.nama AS nama_barang, GROUP_CONCAT(saldo_qty ORDER BY rn DESC LIMIT 1) AS sisa_stok, GROUP_CONCAT(stok_min ORDER BY rn ASC LIMIT 1) AS stok_min, GROUP_CONCAT(satuan_terkecil ORDER BY rn ASC LIMIT 1) AS satuan_terkecil FROM v_t_result_table INNER JOIN m_barang ON v_t_result_table.kd_barang = m_barang.kd_barang WHERE DATE(tanggal) <= '",akhir,"' AND (m_barang.kd_barang LIKE '%",search,"%' OR m_barang.nama LIKE '%",search,"%') GROUP BY v_t_result_table.kd_barang, m_barang.nama ORDER BY TRIM(m_barang.nama) ASC) sisa_stok	
	) for_alias ",query_limit," ");
			 
ELSEIF ( in_jenis = 3 ) THEN
SET sql_query = CONCAT("SELECT ",sql_final," FROM (
			SELECT 
				(SELECT nama FROM m_barang WHERE kd_barang=a_sisa_stok.kd_barang) AS nama_barang,
				a_sisa_stok.*, IFNULL(total_sales,0) AS total_sales, IFNULL(qty_sales,0) AS qty_sales
			FROM
				(SELECT kd_barang, GROUP_CONCAT(saldo_qty ORDER BY rn DESC LIMIT 1) AS sisa_stok, GROUP_CONCAT(saldo_rp ORDER BY rn DESC LIMIT 1) AS nominal_persediaan, GROUP_CONCAT(satuan_terkecil ORDER BY rn DESC LIMIT 1) AS satuan_terkecil FROM v_t_result_table WHERE DATE(tanggal) <= '",akhir,"' GROUP BY kd_barang ) a_sisa_stok
						LEFT JOIN 
						( 	
							SELECT kd_barang, SUM(bersih) AS total_sales, SUM(qty_keluar) AS qty_sales FROM v_t_result_table WHERE jenis=7 AND DATE(tanggal) <= '",akhir,"'
							GROUP BY kd_barang 
						) bersih ON a_sisa_stok.kd_barang = bersih.kd_barang
				) for_alias WHERE kd_barang LIKE '%",search,"%' OR nama_barang LIKE '%",search,"%'
	 ORDER BY sisa_stok DESC ",query_limit," ");
				 
ELSEIF ( in_jenis = 4 ) THEN
	 SET sql_query = CONCAT("SELECT ",sql_final," FROM (
			SELECT 
				(SELECT nama FROM m_barang WHERE kd_barang=sisa_stok.kd_barang) AS nama_barang,
				sisa_stok.*, saldo_awal_rp, saldo_awal_qty, saldo_akhir_rp, saldo_akhir_qty, IFNULL(qty_masuk,0) AS debet_qty, IFNULL(rupiah_masuk,0) AS debet_rp, IFNULL(qty_keluar,0) AS kredit_qty, IFNULL(rupiah_keluar,0) AS kredit_rp
			FROM
				(SELECT kd_barang, satuan_terkecil, GROUP_CONCAT(saldo_qty ORDER BY rn DESC LIMIT 1) AS sisa_stok, GROUP_CONCAT(saldo_rp ORDER BY rn DESC LIMIT 1) AS nominal_persediaan FROM v_t_result_table WHERE DATE(tanggal) <= '",akhir,"' GROUP BY kd_barang ) sisa_stok
						LEFT JOIN 
						(
							SELECT kd_barang, GROUP_CONCAT(saldo_rp ORDER BY rn DESC LIMIT 1) AS saldo_awal_rp, GROUP_CONCAT(saldo_qty ORDER BY rn DESC LIMIT 1) AS saldo_awal_qty FROM v_t_result_table WHERE DATE(tanggal) < '",awal,"' GROUP BY kd_barang, satuan_terkecil
						) saldo_awal ON sisa_stok.kd_barang = saldo_awal.kd_barang
						LEFT JOIN 
						(
							SELECT kd_barang, GROUP_CONCAT(saldo_rp ORDER BY rn ASC LIMIT 1) AS saldo_akhir_rp, GROUP_CONCAT(saldo_qty ORDER BY rn ASC LIMIT 1) AS saldo_akhir_qty FROM v_t_result_table WHERE DATE(tanggal) <= '",akhir,"' GROUP BY kd_barang
						) saldo_akhir ON sisa_stok.kd_barang = saldo_akhir.kd_barang
						LEFT JOIN
						(
							SELECT kd_barang, SUM(qty_masuk) AS qty_masuk, SUM(qty_keluar) AS qty_keluar, SUM(qty_masuk*rupiah_masuk) AS rupiah_masuk, SUM(qty_keluar*rupiah_keluar) AS rupiah_keluar FROM v_t_result_table WHERE DATE(tanggal) BETWEEN '",awal,"' AND '",akhir,"' GROUP BY kd_barang
						) debet_kredit ON saldo_awal.kd_barang = debet_kredit.kd_barang
				) for_alias WHERE kd_barang LIKE '%",search,"%' OR nama_barang LIKE '%",search,"%' ORDER BY TRIM(nama_barang) ASC ",query_limit," ");

ELSEIF ( in_jenis = 5 ) THEN
SET sql_query = CONCAT("SELECT ",sql_final," FROM (
			SELECT 
				(SELECT nama FROM m_barang WHERE kd_barang=sisa_stok.kd_barang) AS nama_barang,
				sisa_stok.*, 
				IFNULL(terakhir_jual.tanggal,'-') AS tgl_jual_terakhir, 
				IFNULL(terakhir_beli.tanggal,'-') AS tgl_terakhir_beli 
			FROM
				(SELECT kd_barang, GROUP_CONCAT(saldo_qty ORDER BY rn DESC LIMIT 1) AS sisa_stok, GROUP_CONCAT(satuan_terkecil ORDER BY rn DESC LIMIT 1) AS satuan_terkecil FROM v_t_result_table WHERE DATE(tanggal) <= '",akhir,"' GROUP BY kd_barang ) sisa_stok
						LEFT JOIN 
						(
							SELECT kd_barang, GROUP_CONCAT(tanggal ORDER BY rn DESC LIMIT 1) AS tanggal FROM v_t_result_table WHERE 
							DATE(tanggal) <='",akhir,"' AND jenis=7 GROUP BY kd_barang
						) terakhir_jual ON sisa_stok.kd_barang = terakhir_jual.kd_barang
						LEFT JOIN 
						(
							SELECT kd_barang, tanggal, GetHargaBersih(GetHargaBersih(harga, ddiskon1, ddiskon2, ddiskon3, ddiskon4, 0, 0), mdiskon1, mdiskon2, mdiskon3, mdiskon4, mpajak, mppnbm) / jumlah AS last_purchase FROM
							(
							SELECT
								mbs.kd_barang,
								GROUP_CONCAT( mbs.jumlah ORDER BY DATE ( tanggal ) DESC, harga_beli DESC LIMIT 1 ) AS jumlah,
								GROUP_CONCAT( t_pembelian.tanggal ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS tanggal,
								GROUP_CONCAT( harga_beli ORDER BY DATE ( tanggal ) DESC, harga_beli DESC LIMIT 1 ) AS harga,
								GROUP_CONCAT( t_pembelian_detail.kd_satuan ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS kd_satuan,
								GROUP_CONCAT( t_pembelian.diskon1 ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS mdiskon1,
								GROUP_CONCAT( t_pembelian.diskon2 ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS mdiskon2,
								GROUP_CONCAT( t_pembelian.diskon3 ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS mdiskon3,	
								GROUP_CONCAT( t_pembelian.diskon4 ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS mdiskon4,
								GROUP_CONCAT( t_pembelian.pajak ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS mpajak,
								GROUP_CONCAT( t_pembelian.ppnbm ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS mppnbm,
								GROUP_CONCAT( t_pembelian_detail.diskon1 ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS ddiskon1,
								GROUP_CONCAT( t_pembelian_detail.diskon2 ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS ddiskon2,
								GROUP_CONCAT( t_pembelian_detail.diskon3 ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS ddiskon3,	
								GROUP_CONCAT( t_pembelian_detail.diskon4 ORDER BY DATE ( tanggal ) DESC LIMIT 1 ) AS ddiskon4	
							FROM
								t_pembelian
								INNER JOIN t_pembelian_detail ON t_pembelian.no_transaksi = t_pembelian_detail.no_transaksi 
								INNER JOIN m_barang_satuan mbs ON t_pembelian_detail.kd_barang=mbs.kd_barang AND t_pembelian_detail.kd_satuan=mbs.kd_satuan
							WHERE
								DATE ( tanggal ) <= '",akhir,"'
							GROUP BY
								mbs.kd_barang
							) a
						) terakhir_beli ON sisa_stok.kd_barang = terakhir_beli.kd_barang) for_alias WHERE kd_barang LIKE '%",search,"%' OR nama_barang LIKE '%",search,"%' ORDER BY tgl_jual_terakhir DESC
			",query_limit," ");

ELSEIF ( in_jenis = 6 ) THEN
SET sql_query = CONCAT("SELECT ",sql_final," FROM (
		SELECT
			( SELECT nama FROM m_barang WHERE kd_barang = a.kd_barang ) AS nama_barang,
			a.* 
		FROM
			( SELECT kd_barang, GROUP_CONCAT( harga ORDER BY rn DESC LIMIT 1 ) AS harga FROM v_t_result_table GROUP BY kd_barang ) a ) for_alias WHERE kd_barang LIKE '%",search,"%' OR nama_barang LIKE '%",search,"%' ORDER BY TRIM(nama_barang) ASC ",query_limit," ");
END IF;

-- select sql_query;
PREPARE prepared_stmt FROM sql_query;
EXECUTE prepared_stmt;
	

END