#include "Chorus.h"

ModDelay::ModDelay(float defaultDryWet,float defaultRate,float defaultDepth,bool defaultTrueByPass)
{
	setDryWet(defaultDryWet);
	setRate(defaultRate);
	setDepth(defaultDepth);
	setTrueByPass(defaultTrueByPass);
}

ModDelay::~ModDelay()
{
}

void ModDelay::prepareToPlay(double newSampleRate, float maxNumSamples,int numCh)
{
	sampleRate = newSampleRate;
	memorySize = roundToInt(MAX_DELAY_TIME * sampleRate) + maxNumSamples;
	delayMemory.setSize(2, memorySize);
	delayMemory.clear();
	modulation.setSize(2, maxNumSamples);
	modulation.clear();

	drywetter.prepareToPlay(newSampleRate, roundToInt(maxNumSamples));

	truebypass.prepareToPlay(sampleRate, maxNumSamples, numCh);

	rate.prepareToPlay(sampleRate);

	depth.prepareToPlay(sampleRate);
	setFixDelayTime(20.0);//DelayTime per la Depth, quindi non viene usata come parametro
}

void ModDelay::processBlock(AudioBuffer<float>& buffer)
{
	drywetter.copyDrySignal(buffer);
	truebypass.copyInputBuffer(buffer);

	//Sintesi della modulazione
	const auto numSamples = buffer.getNumSamples();
	rate.getNextAudioBlock(modulation, numSamples);
	depth.processBlock(modulation, numSamples);

	//check modulation signal
	for (int ch = 0; ch < buffer.getNumChannels(); ++ch)
	{
		FloatVectorOperations::min(modulation.getWritePointer(ch), modulation.getWritePointer(ch), MAX_DELAY_TIME, numSamples);
	}

	const auto numCh = buffer.getNumChannels();
	const auto numInputSamples = buffer.getNumSamples();

	auto bufferData = buffer.getArrayOfWritePointers();
	auto delayData = delayMemory.getArrayOfWritePointers();
	auto modData = modulation.getArrayOfWritePointers();

	auto numModCh = modulation.getNumChannels();

	for (int smp = 0; smp < numInputSamples; ++smp)
	{
		//Smoothed Value
		for (int ch = 0; ch < numCh; ++ch)
		{

			auto dt = modData[jmin(ch, numModCh - 1)][smp];

			//With SmoothedValue
			auto readIndex = writeIndex - (dt * sampleRate);


			auto integerPart = static_cast<int>(readIndex);
			auto fractionalPart = readIndex - integerPart;
			auto alpha = fractionalPart / (2.0 - fractionalPart);

			auto A = (integerPart + memorySize) % memorySize;
			auto B = (A + 1) % memorySize;

			//Input -> delay Memory
			delayData[ch][writeIndex] = bufferData[ch][smp];

			//FRACTIONAL DELAY - AllPass Interpolation
			auto sampleValue = alpha * (delayData[ch][B] - oldSample[ch]) + delayData[ch][A];
			oldSample[ch] = sampleValue;

			//Delay Memory -> buffer
			bufferData[ch][smp] = sampleValue;
		}
		++writeIndex %= memorySize;
	}
	drywetter.mixDrySignal(buffer);
	truebypass.applyTB(buffer);
}

void ModDelay::releaseResources()
{
	delayMemory.setSize(0, 0);
	memorySize = 0;
	modulation.setSize(0, 0);

	drywetter.releaseResources();

	truebypass.releaseResources();
}

void ModDelay::setDryWet(float newValue)
{
	drywetter.setDWRatio(newValue);
}

void ModDelay::setRate(float newValue)
{
	rate.setFrequency(newValue);
}

void ModDelay::setDepth(float newValue)
{
	depth.setModAmount(newValue*0.00005f);
}

void ModDelay::setFixDelayTime(float newValue)
{
	depth.setFixParameter(newValue*0.001);
}

void ModDelay::setTrueByPass(float newValue)
{
	truebypass.setPower(newValue);
}
