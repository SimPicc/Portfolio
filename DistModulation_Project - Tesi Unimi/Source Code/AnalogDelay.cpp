#include "AnalogDelay.h"

AnalogDelay::AnalogDelay(double defaultDelayTime, float defaultFeedback, float defaultDryWet, float defaultTrueBypass, float defaultTone)
{
	feedback.setCurrentAndTargetValue(defaultFeedback);
	delayTime.setCurrentAndTargetValue(defaultDelayTime);

	setDryWet(defaultDryWet);
	setTrueByPass(defaultTrueBypass);
	setTone(defaultTone);
}

AnalogDelay::~AnalogDelay() {}

void AnalogDelay::prepareToPlay(double newSampleRate, float maxNumSamples, int numChannels)
{
	truebypass.prepareToPlay(newSampleRate, roundToInt(maxNumSamples), numChannels);
	sampleRate = newSampleRate;
	memorySize = roundToInt(MAX_DELAY_TIME * sampleRate) + maxNumSamples;
	delayMemory.setSize(2, memorySize);
	delayMemory.clear();

	feedback.reset(sampleRate, 0.300);
	delayTime.reset(sampleRate, 0.025);
	drywetter.prepareToPlay(sampleRate, maxNumSamples);

	juce::dsp::ProcessSpec spec{ sampleRate,maxNumSamples,numChannels };
	tone.prepareToPlay(sampleRate, spec);
}

void AnalogDelay::processBlock(AudioBuffer<float>& buffer)
{
	truebypass.copyInputBuffer(buffer);
	drywetter.copyDrySignal(buffer);

	const auto numCh = buffer.getNumChannels();
	const auto numInputSamples = buffer.getNumSamples();

	auto bufferData = buffer.getArrayOfWritePointers();
	auto delayData = delayMemory.getArrayOfWritePointers();

	tone.updateToneGain(tone.getLastSampleRate(), tone.getCurrentToneValue());
	tone.skipBlock(buffer.getNumSamples());

	for (int smp = 0; smp < numInputSamples; ++smp)
	{
		//Smoothed Value
		auto dt = delayTime.getNextValue();
		auto fb = feedback.getNextValue();

		//With SmoothedValue
		auto readIndex = writeIndex - (dt * sampleRate);


		auto integerPart = static_cast<int>(readIndex);
		auto fractionalPart = readIndex - integerPart;
		auto alpha = fractionalPart / (2.0 - fractionalPart);

		auto A = (integerPart + memorySize) % memorySize;
		auto B = (A + 1) % memorySize;

		for (int ch = 0; ch < numCh; ++ch)
		{
			//Input -> delay Memory
			delayData[ch][writeIndex] = bufferData[ch][smp];

			//FRACTIONAL DELAY - AllPass Interpolation
			auto sampleValue = alpha * (delayData[ch][B] - oldSample[ch]) + delayData[ch][A];
			oldSample[ch] = sampleValue;

			//Delay Memory -> buffer

			bufferData[ch][smp] = sampleValue;

			//Feedback

			//SmoothedValue
			delayData[ch][writeIndex] += sampleValue * fb;

		}
		++writeIndex %= memorySize;
	}
	dsp::AudioBlock<float>block(buffer);
	dsp::ProcessContextReplacing<float> context(block);
	tone.shelfFilterProcess(context);

	drywetter.mixDrySignal(buffer);
	
	truebypass.applyTB(buffer);
}

void AnalogDelay::releaseResources()
{
	delayMemory.setSize(0, 0);
	memorySize = 0;

	drywetter.releaseResources();
	truebypass.releaseResources();
}


void AnalogDelay::setTime(double newValue)
{
	//delayTime = newValue;
	delayTime.setTargetValue(newValue);
}

void AnalogDelay::setFeedback(float newValue)
{
	//feedback = newValue;
	feedback.setTargetValue(newValue);
}

void AnalogDelay::setTrueByPass(float newValue)
{
	truebypass.setPower(newValue);
}

void AnalogDelay::setDryWet(float newValue)
{
	drywetter.setDWRatio(newValue);
}

void AnalogDelay::setTone(float newValue)
{
	tone.setTargetTone(newValue);
}