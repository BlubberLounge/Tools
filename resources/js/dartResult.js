/**
 * @author Maximilian Mewes
 *
 *
 */

$(function()
{
    // cool, ultra lightweight, but only supports click event
    // let confetti = new Confetti('confetti');
    // confetti.setCount(75);
    // confetti.setSize(1);
    // confetti.setPower(25);
    // confetti.setFade(false);
    // confetti.destroyTarget(false);

    var count = 300;
    var defaults = {
      origin: { y: .35 }
    };

    function fire(particleRatio, opts) {
      confetti(Object.assign({}, defaults, opts, {
        particleCount: Math.floor(count * particleRatio)
      }));
    }

    fire(0.25, {
      spread: 26,
      startVelocity: 55,
    });
    fire(0.2, {
      spread: 60,
    });
    fire(0.35, {
      spread: 100,
      decay: 0.91,
      scalar: 0.8
    });
    fire(0.1, {
      spread: 120,
      startVelocity: 25,
      decay: 0.92,
      scalar: 1.2
    });
    fire(0.1, {
      spread: 120,
      startVelocity: 45,
    });
});
