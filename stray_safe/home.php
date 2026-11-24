<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Homepage</title>
        <link rel="stylesheet" href="home.css">
    </head>
    <body>
       <?php include 'navbar.php'; ?>

       <div class="description">
            <div class="quote-container">
                <h2>Community Animal Rescue</h2>
                <p>Connecting compassionate citizens to report stray animals, facilitate safe adoptions, and report animal mistreatment across local communities.</p>
                <img src="paw-quote.png" alt="quote paw">
            </div>
            <div class="add-info">
                <h2>Every Animal Deserves <span>Care</span></h2>
                <p>Help make stray animals safer by sharing information, finding pets in need, and <br> connecting them with people who can help.</p>
            </div>

           <div id="com-btn" onclick="window.location.href='community.php'">
                Open Community Feed
            </div>

            <div id="circle"></div>
            <div id="ellipse"></div>
            
            <div id="homepost">
                <div class="container">
                   <div class="sub-one">
                        <div class="left-wing">
                            <div class="user-header">
                                <img src="pfp.jpg" id="pfp" alt="Profile">
                                <p><span>User_001</span>October 15, 2026</p>
                            </div>
                            <div class="pet-photos">
                                <img src="pet1.jpg" class="feed" alt="Pet 1"/>
                                <img src="pet2.jpg" class="feed" alt="Pet 2"/>
                            </div>
                        </div>
                        <div class="right-wing">
                            <p id="p1">Public Market, San <br>Fernando, Pampanga</p>
                            <p id="p2">View Specific Location</p>
                            <p id="p3">Spotted this friendly-looking dog and pigs near the public market. It looks so cute. Sharing in case someone recognizes it.<br><br>Condition: Needs assistance</p>
                        </div>
                   </div>
                   <div class="sub-two">
                        <div class="react">❤️ 12</div>
                        <div class="react">💬 5</div>
                        <div class="react">➤ Share</div>
                   </div>
                </div>
            </div>
       </div>

            <div class="features-count">
                <div class="features"><span>01</span> Share a sighting</div>
                <div class="features"><span>02</span> Connect with others</div>
                <div class="features"><span>03</span> Support animal welfare</div>
            </div>

            <div class="information-box">
                <div class="sub-info-box1">
                    <p class="t1">How StraySafe Helps</p>
                    <p class="t2">One place makes a difference</p>
                    <p class="t3">Use the website to discover animals, share useful <br>information, or explore responsible homing.</p>
                </div>
                <div class="sub-info-box2">
                    <a class="box" href="community.php">
                        <h3>Browse Animals</h3>
                        <img src="box1.png" alt="Browse">
                        <p>View community animal profiles and <br>their reported locations.</p>
                        <h4>Community Feed ➔</h4>
                    </a>
                    <a class="box" href="find.php">
                        <h3>Find a Pet</h3>
                        <img src="box2.png" alt="Find">
                        <p>See about animals that may be ready for <br>adoption.</p>
                        <h4>Find a Pet ➔</h4>
                    </a>
                    <a class="box" href="publish.php">
                        <h3>Report a Stray</h3>
                        <img src="box3.png" alt="Report">
                        <p>Share a sighting so the community <br>can be more aware.</p>
                        <h4>Publish ➔</h4>
                    </a>
                </div>
                <div class="sub-info-box3">
                    <div class="last-box">
                        <div class="last-box-text">
                            <span>be a part of change</span>
                            <h3>A safer community starts with awareness.</h3>
                            <p>Responsible care can begin with simply sharing accurate information.</p>
                        </div>
                        <div class="learn" onclick="window.location.href='about.php'">
                            <a>Learn about StraySafe</a>
                        </div>
                    </div>
                </div>
            </div>

            <footer>
                <div class="footer-box f1">
                    <img src="paw.png" alt="Logo">
                    <p>StraySafe</p>
                </div>
                <div class="footer-box f2">
                    <p>Information and Welfare Tracking System for Community Animals</p>
                </div>
                <div class="footer-box f3">
                    <p>Student project</p>
                </div>
            </footer>
        <script src="script.js"></script>
    </body>
</html>