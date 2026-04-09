<?php
include 'db.php';
$user_id = mysqli_real_escape_string($conn, $_GET['user_id']);

$query = "SELECT * FROM payments WHERE user_id = '$user_id' ORDER BY created_at DESC";
$res = mysqli_query($conn, $query);

if(mysqli_num_rows($res) > 0) {
    while($p = mysqli_fetch_assoc($res)) {
        $isPaid = ($p['status'] == 'paid');
        $color = $isPaid ? 'emerald' : 'amber';
        echo '
        <div class="mb-10 relative pl-8 border-l-2 border-slate-100 last:border-0 pb-2">
            <div class="absolute -left-[9px] top-0 w-4 h-4 rounded-full border-4 border-white bg-'.$color.'-500 shadow-sm shadow-'.$color.'-200"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-[10px] font-black uppercase text-slate-400 tracking-wider">'.date('M d, Y • h:i A', strtotime($p['created_at'])).'</p>
                    <h4 class="text-lg font-bold text-slate-900 mt-1">₱'.number_format($p['amount'], 2).'</h4>
                </div>
                <span class="text-[9px] px-2.5 py-1 rounded-lg font-black uppercase tracking-widest bg-'.$color.'-50 text-'.$color.'-600 border border-'.$color.'-100">
                    '.$p['status'].'
                </span>
            </div>
            <div class="mt-4 bg-slate-50 rounded-2xl p-4 border border-slate-100">
                <p class="text-[10px] text-slate-400 font-bold uppercase mb-1">Transaction Details</p>
                <p class="text-xs font-semibold text-slate-600">Ref: <span class="text-slate-900">'.($p['reference_number'] ?: 'N/A').'</span></p>
                <p class="text-xs font-semibold text-slate-600 mt-1">Method: <span class="text-slate-900 uppercase">'.$p['payment_method'].'</span></p>
            </div>
        </div>';
    }
} else {
    echo '
    <div class="flex flex-col items-center justify-center py-20 text-center">
        <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mb-4 text-slate-200">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
        </div>
        <p class="text-slate-400 font-bold text-sm">No payment history found for this student.</p>
    </div>';
}
?>