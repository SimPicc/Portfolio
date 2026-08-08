#include "ModulationChorus.h"

NaiveOscillator::NaiveOscillator(double defaultFrequency)
{
	frequency.setCurrentAndTargetValue(defaultFrequency);
}

NaiveOscillator::~NaiveOscillator() {}

void NaiveOscillator::prepareToPlay(double sampleRate)
{
	samplePeriod = 1.0 / sampleRate;
	frequency.reset(sampleRate, 0.02);
}

void NaiveOscillator::setFrequency(double newValue)
{
	jassert(newValue > 0.0);
	frequency.setTargetValue(newValue);
}

void NaiveOscillator::getNextAudioBlock(AudioBuffer<double>& buffer, const int numSamples)
{
	jassert(buffer.getNumChannels() >= 2);

	const int numCh = buffer.getNumChannels();
	auto data = buffer.getArrayOfWritePointers();

	double leftSample = 0.0;
	double rightSample = 0.0;

	for (int s = 0; s < numSamples; ++s)
	{
		getNextAudioSample2(leftSample, rightSample);

		data[0][s] = leftSample;
		data[1][s] = rightSample;

	}
}

void NaiveOscillator::getNextAudioSample2(double& leftSample, double& rightSample)
{
	double rightPhase = currentPhase + 0.25;

	leftSample = sin(currentPhase * MathConstants<double>::twoPi);
	rightSample = sin(rightPhase * MathConstants<double>::twoPi);

	double phaseIncrement = frequency.getNextValue() * samplePeriod;
	currentPhase += phaseIncrement;
	currentPhase -= static_cast<int>(currentPhase);
}


ParameterModulation::ParameterModulation(const double defaultParameter, const double defaultModAmount)
{
	parameter.setCurrentAndTargetValue(defaultParameter);
	modAmount.setCurrentAndTargetValue(defaultModAmount);
}

ParameterModulation::~ParameterModulation() {}

void ParameterModulation::prepareToPlay(double sampleRate)
{
	parameter.reset(sampleRate, 0.02);
	modAmount.reset(sampleRate, 0.02);
}

void ParameterModulation::setModAmount(double newValue)
{
	modAmount.setTargetValue(newValue);
}

void ParameterModulation::setParameter(const double newValue)
{
	parameter.setTargetValue(newValue);
}

void ParameterModulation::setFixParameter(const double newValue)
{
	parameter.setCurrentAndTargetValue(newValue);
}


void ParameterModulation::processBlock(AudioBuffer<double>& buffer, const int numSamples)
{
	auto numCh = buffer.getNumChannels();
	auto data = buffer.getArrayOfWritePointers();

	//Riscalo tra 0 e 1
	for (int ch = 0; ch < numCh; ++ch)
	{
		FloatVectorOperations::add(data[ch], 1.00, numSamples);
		FloatVectorOperations::multiply(data[ch], 0.5, numSamples);
	}

	//Riscalo segnale in funzione di modAmount
	modAmount.applyGain(buffer, numSamples);
	//sommo modulazione a valore del parametro
	if (parameter.isSmoothing()) {
		for (int s = 0; s < numSamples; ++s)
		{
			for (int ch = 0; ch < numCh; ++ch)
			{
				data[ch][s] += ch ? parameter.getCurrentValue() : parameter.getNextValue();
			}
		}
	}
	else
	{
		for (int ch = 0; ch < numCh; ++ch)
		{
			FloatVectorOperations::add(data[ch], parameter.getCurrentValue(), numSamples);
		}
	}

}