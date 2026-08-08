#include "PluginProcessor.h"
#include "PluginEditor.h"

DistModulationFxAudioProcessor::DistModulationFxAudioProcessor(int defaultChain)
    : parameters(*this, nullptr, "Params", Parameters::createParameterLayout()),
    fuzz(Parameters::defaultFuzzGain, Parameters::defaultFuzzVolume, Parameters::defaultFuzzBias, Parameters::defaultFuzzPower, Parameters::defaultFuzzTone, Parameters::defaultFuzzHP),
    delay(Parameters::defaultDelayTime,Parameters::defaultDelayFeedback,Parameters::defaultDelayDryWet,Parameters::defaultDelayPower,Parameters::defaultDelayTone),
    chorus(Parameters::defaultChorusDryWet,Parameters::defaultChorusRate,Parameters::defaultChorusDepth,Parameters::defaultChorusPower)
{
    chain=Parameters::defaultChain;
    Parameters::addListenerToAllParameters(parameters, this);
}

DistModulationFxAudioProcessor::~DistModulationFxAudioProcessor()
{
}


void DistModulationFxAudioProcessor::prepareToPlay (double sampleRate, int samplesPerBlock)
{
    auto numCh = jmax(getTotalNumInputChannels(), getTotalNumOutputChannels());

    fuzz.prepareToPlay(sampleRate, samplesPerBlock, numCh,*this);
    delay.prepareToPlay(sampleRate, samplesPerBlock, numCh);
    chorus.prepareToPlay(sampleRate, samplesPerBlock, numCh);
}

void DistModulationFxAudioProcessor::releaseResources()
{
    delay.releaseResources();
    chorus.releaseResources();
}

void DistModulationFxAudioProcessor::processBlock (juce::AudioBuffer<float>& buffer, juce::MidiBuffer& midiMessages)
{
    juce::ScopedNoDenormals noDenormals;
    
    switch (chain)
    {
    case 0:
        fuzz.processBlock(buffer);
        chorus.processBlock(buffer);
        delay.processBlock(buffer);
        break;
    case 1:        
        fuzz.processBlock(buffer);
        delay.processBlock(buffer);
        chorus.processBlock(buffer);
        break;
    case 2:
        chorus.processBlock(buffer);
        fuzz.processBlock(buffer);
        delay.processBlock(buffer);
        break;
    case 3:
        chorus.processBlock(buffer);
        delay.processBlock(buffer);
        fuzz.processBlock(buffer);
        break;
    case 4:
        delay.processBlock(buffer);
        fuzz.processBlock(buffer);
        chorus.processBlock(buffer);
        break;
    case 5:
        delay.processBlock(buffer);
        chorus.processBlock(buffer);
        fuzz.processBlock(buffer);
        break;
    default:
        chain = 0;
        break;
    }
}

void DistModulationFxAudioProcessor::setChain(int newValue)
{
    chain = roundToInt(newValue);
}

void DistModulationFxAudioProcessor::parameterChanged(const String& paramID, float newValue)
{
    //FUZZ
    if (paramID == Parameters::nameFuzzPower)
    {
        fuzz.setTrueByPass(newValue);
    }
    if (paramID == Parameters::nameFuzzGain)
    {
        fuzz.setGain(newValue);
    }
    if (paramID == Parameters::nameFuzzVolume)
    {
        fuzz.setVolume(newValue);
    }
    if (paramID == Parameters::nameFuzzTone)
    {
        fuzz.setTone(newValue);
    }
    if (paramID == Parameters::nameFuzzBias)
    {
        fuzz.setBias(newValue);
    }
    if (paramID == Parameters::nameFuzzType)
    {
        fuzz.setTypeDistortion(newValue);
    }
    if (paramID == Parameters::nameFuzzHP)
    {
        fuzz.setHighPassPower((roundToInt(newValue)));
    }

    //DELAY
    if (paramID == Parameters::nameDelayPower)
    {
        delay.setTrueByPass(newValue);
    }
    if (paramID == Parameters::nameDelayTime)
    {
        delay.setTime(newValue);
    }
    if (paramID == Parameters::nameDelayFeedback)
    {
        delay.setFeedback(newValue);
    }
    if (paramID == Parameters::nameDelayDryWet)
    {
        delay.setDryWet(newValue);
    }
    if (paramID == Parameters::nameDelayTone)
    {
        delay.setTone(newValue);
    }

    //CHORUS
    if (paramID == Parameters::nameChorusPower)
    {
        chorus.setTrueByPass(newValue);
    }
    if (paramID == Parameters::nameChorusRate)
    {
        chorus.setRate(newValue);
    }
    if (paramID == Parameters::nameChorusDepth)
    {
        chorus.setDepth(newValue);
    }
    if (paramID == Parameters::nameChorusDryWet)
    {
        chorus.setDryWet(newValue);
    }

    //CHAIN
    if (paramID == Parameters::nameChain)
    {
        setChain(newValue);
    }
}


bool DistModulationFxAudioProcessor::hasEditor() const
{
    return true;
}

juce::AudioProcessorEditor* DistModulationFxAudioProcessor::createEditor()
{
    return new DistModulationFxAudioProcessorEditor (*this,parameters);
}

//==============================================================================
void DistModulationFxAudioProcessor::getStateInformation (juce::MemoryBlock& destData)
{
    auto state = parameters.copyState();
    std::unique_ptr<XmlElement> xml(state.createXml());
    copyXmlToBinary(*xml, destData);
}

void DistModulationFxAudioProcessor::setStateInformation (const void* data, int sizeInBytes)
{
    std::unique_ptr<XmlElement> xmlState(getXmlFromBinary(data, sizeInBytes));
    if (xmlState.get() != nullptr)
        if (xmlState->hasTagName(parameters.state.getType()))
            parameters.replaceState(ValueTree::fromXml(*xmlState));
}

//==============================================================================
// This creates new instances of the plugin..
juce::AudioProcessor* JUCE_CALLTYPE createPluginFilter()
{
    return new DistModulationFxAudioProcessor();
}
