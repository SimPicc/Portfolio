(
s.boot;
s.freqscope;
)
s.quit;
Server.killAll;

//Elettricità - Mouse FM
(
SynthDef(\fm_4, {arg fpor=440,fmod=500,mid=80,hpfCutoff=1000;
	                var mod,por,filtered,playSound;
	                    //fmod = MouseX.kr(300,500);
	                    //mid  = MouseY.kr(30,50);
	                    mod  = fpor + (SinOsc.ar(fmod) * fmod * mid);
	                    por = SinOsc.ar([mod,mod+3]);
	                    playSound=MouseX.kr(0,1);
	                    filtered = HPF.ar(por, hpfCutoff);
	                Out.ar(0,filtered*playSound);
                 }).add;
)
c = Synth(\fm_4);
c.free;


//Elettricità - FM
(
SynthDef(\fm_5, {arg fpor=440,fmod=500,mid=80,hpfCutoff=150;
	                var mod,por,filtered,env;
	                    //fmod = MouseX.kr(300,500);
	                    //mid  = MouseY.kr(30,50);
	                    mod  = fpor + (SinOsc.ar(fmod) * fmod * mid);
	                    por = SinOsc.ar([mod,mod+3]);
	                    filtered = HPF.ar(por, hpfCutoff);
	                    env=Line.kr(1,0,0.4);
	                Out.ar(0,filtered*env);
                 }).add;
)



Synth(\fm_5, [\fpor,rrand(300,900)]);


//Movimento - Noise+Filter
(
SynthDef(\Noise,{arg mult=1,lpfCutoff=600;
		           var noise,filtered,env;
		  noise=WhiteNoise.ar(mult);
	      filtered = LPF.ar(noise, lpfCutoff);
	      //env=Line.kr(1,0,0.65);
	      env = EnvGen.ar(Env.perc(0.20,0.45),doneAction:2);
	      filtered=Pan2.ar(filtered*env,0);
	Out.ar(0,filtered);
	}).add;
)


Synth(\Noise, [\bpfCutoff,rrand(500,1000)]);
a=Synth(\Noise);
a.free;


//Base Sonora - Pad Additiva
(
SynthDef(\addi_1,{arg parz = #[1, 2, 3, 4, 6, 8, 10, 12, 16],   // Literal Array
	                  fond = 110,
	                  amps = #[0.8, 0.7, 0.9, 0.5, 0.1, 0.04, 0.01, 0.02, 0.1],
	                  dur = 3,
	                  pan = 0, amp = 0.5;
	              var sig, env,filtered;
	                  sig = SinOsc.ar(parz * fond, // Freqs
		                              0,           // Fasi
		                              amps);       // Ampiezze
	                  sig = Mix(sig);
	                  env = EnvGen.ar(Env.perc(4,dur-2),doneAction:2);
	                  //env=MouseY.kr(0,1).poll(3);
	                  sig=sig*env;
	                  filtered=LPF.ar(sig,500);
	                  filtered = Pan2.ar(filtered, pan);
	               Out.ar(0, filtered * amp);          // Ampiezza globale
                   }).add;
)

a=Synth(\addi_1);
a.set(\fond,160);
a.free;


//Fastidio - Routine AM
(
SynthDef.new(\am, {arg amp=1,fpor=200,fmod=400,lpfCutoff=800;
	                  var por, mod, sig,env,filtered;
	                      por = SinOsc.ar(fpor);
		                     mod = SinOsc.ar(fmod).unipolar;
	                      sig = por * mod * amp;
	                      env=Line.kr(1,0,0.20);
	                      sig = Pan2.ar(sig*env, 0);
	                      filtered=LPF.ar(sig,lpfCutoff);
	                  Out.ar(0,filtered);
}).add
)
a=Synth(\am);
a.free;
(
~lpf=[300,400,500,600,700,800,900];
r = Routine.new({
	            inf.do({
		a=Synth(\am,[\amp, rand(0.3,1),\fmod,rand(220,440),\fpor,220,\lpfCutoff,~lpf.choose]);0.25.wait;
		               })
                }).play
)
r.stop;


//Impatto - Kick Sound PWM
(
SynthDef(\pulse_7, {arg freq=55,amp=1,pan=0,duty=0.78;
	                var sig,env,pann,filtered;
	                    sig   = VarSaw.ar(freq,0,duty,2.5);
	                    env   = Line.kr(1,0,0.7);
	                    sig=sig*amp*env;
	                    pann  = Pan2.ar(sig,pan);
	                Out.ar(0,pann)}
         ).add;
)

a = Synth(\pulse_7);
a.free;


//Interfernza - PWM
(
SynthDef(\pulse_rythm, {arg freq=110,amp=1,pan=0,duty=0.20;
	                var sig,env,pann,filtered;
	                    sig   = VarSaw.ar(freq,0,duty,2.5);
	                    //env   = Line.kr(1,0,0.7);
	                    env = EnvGen.ar(Env.perc(0.08,0.1),doneAction:2);
	                    sig=sig*amp*env;
	                    pann  = Pan2.ar(sig,pan);
	                Out.ar(0,pann)}
         ).add;
)
a=Synth(\pulse_rythm);
a.free;
(
~dut=[0.20,0.33,0.10];
r = Routine.new({
	            inf.do({
		a=Synth(\pulse_rythm,[\amp, 1,\duty,~dut.choose]);0.20.wait;
		               })
                }).play
)
r.stop;
