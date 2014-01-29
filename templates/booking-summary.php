<ul>
    <li>From: <?php echo $booking->getFromDateString('d M Y'); ?></li>
    <li>To: <?php echo $booking->getToDateString('d M Y'); ?></li>
    <li>Adults: <?php echo $booking->getAdults(); ?></li>
    <li>Children: <?php echo $booking->getChildren(); ?></li>
    <li>Infants: <?php echo $booking->getInfants(); ?></li>
    <li>Total: &pound;<?php echo number_format($booking->getFullPrice(), 2); ?></li>
    <?php
        if ($booking->getAmountPaid() == 0) {
            ?>
    <li>Amount to pay: &pound;<?php echo number_format($booking->getDepositAmount(), 2); ?></li>
            <?php
        }
    ?>
</ul>