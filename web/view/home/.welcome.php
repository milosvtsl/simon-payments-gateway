<?php /** @var \View\Login\LoginView $this  **/ ?>
    <section id="intro" class="first">
        <h1>Home Page</h1>

        <?php if($this->hasException()) { ?>
            <h5><?php echo $this->hasException(); ?></h5>

        <?php } else if ($this->hasSessionMessage()) { ?>
            <h5><?php echo $this->popSessionMessage(); ?></h5>

        <?php } else { ?>
            <h5>Under Construction...</h5>

        <?php } ?>
    </section>
