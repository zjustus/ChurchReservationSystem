<?php
class Overlay{
    private static function css($overlayID){ ?>
        <style media="screen">
            <?php echo '#'.$overlayID; ?>{
                position: fixed; /* Sit on top of the page content */
                /* display: none; /* Hidden by default */
                width: 100%; /* Full width (cover the whole page) */
                height: 100%; /* Full height (cover the whole page) */
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: rgba(0,0,0,0.7); /* Black background with opacity */
                z-index: 99; /* Specify a stack order in case you're using a different order for other elements */
                cursor: pointer; /* Add a pointer on hover */
            }
            .overlay-card{
                margin-top: 30%;
            }
        </style>
    <?php }
    private static function deismissJs($overlayID){ ?>
        <script type="text/javascript">
            $("<?php echo '#'.$overlayID; ?>-dismiss").click(function(){
                $("<?php echo '#'.$overlayID; ?>").fadeOut();
            });
        </script>
    <?php }
    private static function showJs($overlayID){ ?>
        <script type="text/javascript">
            $("<?php echo '#'.$overlayID; ?>-show").click(function(){
                $("<?php echo '#'.$overlayID; ?>").fadeIn();
            });
        </script>
    <?php }

    public static function showOverlay($overlayID, $btnText, $btnColor){
        ?>
        <button id="<?php echo $overlayID; ?>-show" class="btn btn-<?php echo $btnColor; ?> btn-block" type="button"><?php echo $btnText; ?></button>
        <?php
        self::showJs($overlayID);
    }

    public static function message($overlayID, $message){
        self::css($overlayID);
        ?>
        <div id="<?php echo $overlayID; ?>">
            <div class="container-fluid">
                <div class="row align-middle justify-content-center">
                    <div class="col-lg-5">
                        <div class="card overlay-card">
                            <div class="cart-body text-center">
                                <?php echo $message; ?>
                                <button type="button" class="btn btn-primary btn-block" id="<?php echo $overlayID; ?>-dismiss" name="button">OK</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        self::deismissJs($overlayID);
    }

    public static function confirmation($overlayID, $message, $postVar, $postVal, $show = false){
        self::css($overlayID);
        ?>
        <div id="<?php echo $overlayID; ?>" <?php if(!$show) echo 'style="display: none;"'; ?>>
            <div class="container-fluid">
                <div class="row align-middle justify-content-center">
                    <div class="col-lg-5">
                        <div class="card overlay-card">
                            <div class="cart-body text-center">
                                <h2><?php echo $message; ?></h2>
                                <br>
                                <form method="post">
                                    <div class="form-row">
                                        <div class="col-6">
                                            <button type="button" class="btn btn-primary btn-block" id="<?php echo $overlayID; ?>-dismiss">No, Go Back!</button>
                                        </div>
                                        <div class="col-6">
                                            <button type="submit" class="btn btn-danger btn-block" name="<?php echo $postVar; ?>" value="<?php echo $postVal; ?>">Yes</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <?php
        self::deismissJs($overlayID);
    }
}
?>
