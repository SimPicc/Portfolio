#include "Distortion.h"
#include "TrueBypass.h"

Distortion::Distortion(float defaultGain,float defaultVolume,float defaultBias,float defaultTrueByPass,float defaultTone, int defaultTypeDistortion, bool defaultHpPower)
{
	gain.setCurrentAndTargetValue(defaultGain);
    volume.setCurrentAndTargetValue(Decibels::decibelsToGain(defaultVolume));
    bias.setCurrentAndTargetValue(defaultBias);
    setTypeDistortion(defaultTypeDistortion);
    setTrueByPass(defaultTrueByPass);
    setTone(defaultTone);
    setHighPassPower(defaultHpPower);
}

Distortion::~Distortion() {}

void Distortion::prepareToPlay(double sampleRate,int samplesPerBlock, int numCh,juce::AudioProcessor& Processor)
{
    data.resize(numCh);
    oversampler.prepareToPlay(sampleRate, samplesPerBlock, numCh);
    Processor.setLatencySamples(oversampler.getLatency());
    auto overSamplingFactor = oversampler.getOversamplingFactor();
    sampleRate *= overSamplingFactor;
    samplesPerBlock *= overSamplingFactor;

    truebypass.prepareToPlay(sampleRate,samplesPerBlock,numCh);

    gain.reset(sampleRate, 0.02f);
    volume.reset(sampleRate, 0.02f);
    bias.reset(sampleRate, 0.02f);

    juce::dsp::ProcessSpec spec{sampleRate,samplesPerBlock,numCh};

    //PRE
    highPassFilter.prepare(spec);
    //POST
    biasHighPassFilter.prepare(spec);
    lowPassFilter.prepare(spec);
    
    //PRE
    highPassFilter.reset();
    //POST
    biasHighPassFilter.reset();
    lowPassFilter.reset();
    
    //PRE
    *highPassFilter.state = *juce::dsp::IIR::Coefficients<float>::makeHighPass(sampleRate, 150.0f);
    //POST
    *biasHighPassFilter.state = *juce::dsp::IIR::Coefficients<float>::makeHighPass(sampleRate, 30.0f);
    *lowPassFilter.state = *juce::dsp::IIR::Coefficients<float>::makeLowPass(sampleRate, 15000.0f);
    //TONE
    tone.prepareToPlay(sampleRate, spec);
}

void Distortion::processBlock(juce::AudioBuffer<float>& buffer)
{
    //OVERSAMPLING
    dsp::AudioBlock<float> block(buffer);
    
    auto overSampledBlock = oversampler.upSample(block);

    auto numChannels = overSampledBlock.getNumChannels();
    auto lenBlock = overSampledBlock.getNumSamples();

    //TRUE-BYPASS
    truebypass.copyInputBuffer(overSampledBlock);

    dsp::ProcessContextReplacing<float> context(overSampledBlock);
    //PRE DISTORTION
    if (highPassPower)
    {
        highPassFilter.process(context);
    }

    //std::vector <float*> data(numChannels);

    tone.updateToneGain(tone.getLastSampleRate(), tone.getCurrentToneValue());
    tone.skipBlock(lenBlock);

    for (auto ch = 0; ch < numChannels; ++ch)
    {
        data[ch] = overSampledBlock.getChannelPointer(ch);
    }

    for (auto s = 0; s < lenBlock; ++s)
    {
        auto currentGain = gain.getNextValue();
        auto currentVolume = volume.getNextValue();
        auto currentBias = bias.getNextValue();

        auto denominator = fuzz1(currentGain);
        denominator = (std::abs(denominator) < 0.001f) ? 0.001f : denominator;
        normGain1 = 1.0f / denominator;
        
        for (auto ch = 0; ch < numChannels; ++ch)
        {
            //FUZZ 1
            auto input = (currentGain * data[ch][s]) + currentBias; //aggiunta bias pre-distortion
            data[ch][s] = fuzz1(input)*normGain1;
            data[ch][s] = juce::jlimit(-1.0f,1.0f,data[ch][s]) * currentVolume;
        }
    }

    //POST DISTORTION
    biasHighPassFilter.process(context);
    lowPassFilter.process(context);
    //TONE
    tone.shelfFilterProcess(context);

    //TRUE-BYPASS
    truebypass.applyTB(overSampledBlock);

    //DOWNSAMPLING
    oversampler.downSample(block);
}

void Distortion::setGain(float newValue)
{
    gain.setTargetValue(newValue);
    normGain1 = 1.0f / fuzz1(gain.getCurrentValue());
}

void Distortion::setVolume(float newValue)
{
    volume.setTargetValue(Decibels::decibelsToGain(newValue));
}

void Distortion::setBias(float newValue)
{
    bias.setTargetValue(newValue);
}

void Distortion::setTone(float newValue)
{
    tone.setTargetTone(newValue);
}

void Distortion::setTrueByPass(float newValue)
{
    truebypass.setPower(newValue);
}

void Distortion::setTypeDistortion(int newValue)
{
    switch (roundToInt(newValue))
    {
    case 0:
        k = 3;
        break;
    case 1:
        k = 10;
        break;
    case 2:
        k = 15;
        break;
    default:
        k = 15;
        break;
    }

    auto denominator = fuzz1(gain.getTargetValue());
    denominator = (std::abs(denominator) < 0.001f) ? 0.001f : denominator;
    normGain1 = 1.0f / denominator;

}

void Distortion::setHighPassPower(bool newValue)
{
    highPassPower=newValue;
}

float Distortion::valueSign(float value)
{
    return (value >= 0.0f) ? 1.0f : -1.0f;
}

float Distortion::fuzz1(float input) 
{
    return valueSign(input) * (1.0f - std::exp(- 1.0f * std::abs(k * input))) /
           (1.0f - std::exp(- k));
}
