<?php
/**
 * @var \CodeIgniter\Pager\PagerRenderer $pager
 */
$pager->setSurroundCount(2); // Menampilkan 2 angka di kiri/kanan halaman aktif
?>

<ul class="pagination m-0 ms-auto">
    <?php if ($pager->hasPrevious()) : ?>
        <li class="page-item">
            <a class="page-link" href="<?= $pager->getPrevious() ?>" aria-label="<?= lang('Pager.previous') ?>">
                <i class="ti ti-chevron-left"></i>
            </a>
        </li>
    <?php else : ?>
        <li class="page-item disabled">
            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                <i class="ti ti-chevron-left"></i>
            </a>
        </li>
    <?php endif ?>

    <?php foreach ($pager->links() as $link) : ?>
        <li class="page-item <?= $link['active'] ? 'active' : '' ?>">
            <a class="page-link" href="<?= $link['uri'] ?>">
                <?= $link['title'] ?>
            </a>
        </li>
    <?php endforeach ?>

    <?php if ($pager->hasNext()) : ?>
        <li class="page-item">
            <a class="page-link" href="<?= $pager->getNext() ?>" aria-label="<?= lang('Pager.next') ?>">
                <i class="ti ti-chevron-right"></i>
            </a>
        </li>
    <?php else : ?>
        <li class="page-item disabled">
            <a class="page-link" href="#" tabindex="-1" aria-disabled="true">
                <i class="ti ti-chevron-right"></i>
            </a>
        </li>
    <?php endif ?>
</ul>