<?php
get_header();
?>
<style>
    header,
    footer {
        display: none;
    }

    .not-found-page {
        width: 100%;
        height: 100vh;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 18px;
    }
</style>
<div class="not-found-page">
    <p>404 | Not Found</p>
</div>
<?php
get_footer();
